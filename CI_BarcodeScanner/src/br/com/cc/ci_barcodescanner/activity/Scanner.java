package br.com.cc.ci_barcodescanner.activity;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Hashtable;

import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.graphics.Bitmap;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.IBinder;
import android.support.v4.content.LocalBroadcastManager;
import android.util.Log;
import android.view.KeyEvent;
import android.view.SurfaceHolder;
import android.view.SurfaceHolder.Callback;
import android.view.SurfaceView;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.ToggleButton;
import br.com.cc.ci_barcodescanner.R;
import br.com.cc.ci_barcodescanner.camera.CameraManager;
import br.com.cc.ci_barcodescanner.camera.CaptureActivityHandler;
import br.com.cc.ci_barcodescanner.network.NetworkHandler;
import br.com.cc.ci_barcodescanner.network.SocketService;
import br.com.cc.ci_barcodescanner.util.FinishListener;
import br.com.cc.ci_barcodescanner.util.SystemUiHider;
import br.com.cc.ci_barcodescanner.view.ViewfinderView;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.DecodeHintType;
import com.google.zxing.Result;

/**
 * An example full-screen activity that shows and hides the system UI (i.e.
 * status bar and navigation/system bar) with user interaction.
 * 
 * @see SystemUiHider
 */
public final class Scanner extends Activity implements Callback,
		OnItemSelectedListener {

	private static final String TAG = Scanner.class.getSimpleName();

	/**
	 * The flags to pass to {@link SystemUiHider#getInstance}.
	 */
	private static final int HIDER_FLAGS = SystemUiHider.FLAG_HIDE_NAVIGATION;

	/**
	 * The instance of the {@link SystemUiHider} for this activity.
	 */
	private SystemUiHider mSystemUiHider;

	private Collection<BarcodeFormat> decodeFormats;
	private Hashtable<DecodeHintType, Object> decodeHints;
	private String characterSet = "utf-8";

	private View controls_view;
	private SurfaceView camera_preview;
	private CameraManager cameraManager;
	private ViewfinderView viewfinderView;
	private Spinner spinner_databases;
	private boolean hasSurface;
	private CaptureActivityHandler handler;

	private boolean isServiceBound;
	private SocketService boundService;

	public static NetworkHandler network;
	ProgressDialog pd;

	int user_id;
	int database_id;
	int dbs_id[];

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		Window window = getWindow();
		window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);
		setContentView(R.layout.activity_scanner);

		controls_view = findViewById(R.id.controls);
		camera_preview = (SurfaceView) findViewById(R.id.camera_preview);
		spinner_databases = (Spinner) findViewById(R.id.spinner_places);

		network = new NetworkHandler(this);

		// Set up an instance of SystemUiHider to control the system UI for
		// this activity.
		mSystemUiHider = SystemUiHider.getInstance(this, camera_preview,
				HIDER_FLAGS);
		mSystemUiHider.setup();
		mSystemUiHider
				.setOnVisibilityChangeListener(new SystemUiHider.OnVisibilityChangeListener() {
					// Cached values.
					int mControlsHeight;
					int mShortAnimTime;

					@Override
					@TargetApi(Build.VERSION_CODES.HONEYCOMB_MR2)
					public void onVisibilityChange(boolean visible) {
						if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB_MR2) {
							// If the ViewPropertyAnimator API is available
							// (Honeycomb MR2 and later), use it to animate the
							// in-layout UI controls at the bottom of the
							// screen.
							if (mControlsHeight == 0) {
								mControlsHeight = controls_view.getHeight();
							}
							if (mShortAnimTime == 0) {
								mShortAnimTime = getResources().getInteger(
										android.R.integer.config_shortAnimTime);
							}
							controls_view
									.animate()
									.translationY(visible ? 0 : mControlsHeight)
									.setDuration(mShortAnimTime);
						} else {
							// If the ViewPropertyAnimator APIs aren't
							// available, simply show or hide the in-layout UI
							// controls.
							controls_view.setVisibility(visible ? View.VISIBLE
									: View.GONE);
						}
					}
				});

		Intent intent = getIntent();
		user_id = intent.getIntExtra("user_id", -1);

		TextView hello_text = (TextView) findViewById(R.id.hello_text);
		hello_text.setText(getString(R.string.hello_text) + " " + user_id);

		hasSurface = false;

		cameraManager = new CameraManager(getApplication());

		viewfinderView = (ViewfinderView) findViewById(R.id.viewfinder_view);
		viewfinderView.setCameraManager(cameraManager);

		decodeFormats = new ArrayList<BarcodeFormat>();
		decodeFormats.add(BarcodeFormat.AZTEC);
		decodeFormats.add(BarcodeFormat.ITF);

		decodeHints = new Hashtable<DecodeHintType, Object>();
		decodeHints.put(DecodeHintType.TRY_HARDER, Boolean.TRUE);

		spinner_databases.setOnItemSelectedListener(this);
	}

	Handler mHideHandler = new Handler();
	Runnable mHideRunnable = new Runnable() {
		@Override
		public void run() {
			mSystemUiHider.hide();
		}
	};

	public Handler getHandler() {
		return handler;
	}

	public ViewfinderView getViewfinderView() {
		return viewfinderView;
	}

	public CameraManager getCameraManager() {
		return cameraManager;
	}

	public void drawViewfinder() {
		viewfinderView.drawViewfinder();
	}

	@Override
	public void onResume() {
		super.onResume();

		if (network.isNetworkAvailable()) {
			doBindService();
		} else {
			finish();
		}

		handler = null;

		SurfaceHolder surfaceHolder = camera_preview.getHolder();
		if (hasSurface) {
			// The activity was paused but not stopped, so the surface still
			// exists. Therefore
			// surfaceCreated() won't be called, so init the camera here.
			initCamera(surfaceHolder);
		} else {
			// Install the callback and wait for surfaceCreated() to init the
			// camera.
			surfaceHolder.addCallback(this);
		}
	}

	@Override
	public void onPause() {
		doUnbindService();

		if (handler != null) {
			handler.quitSynchronously();
			handler = null;
		}

		cameraManager.closeDriver();

		if (!hasSurface) {
			SurfaceHolder surfaceHolder = camera_preview.getHolder();
			surfaceHolder.removeCallback(this);
		}

		super.onPause();
	}

	@Override
	public void surfaceCreated(SurfaceHolder holder) {
		if (holder == null) {
			Log.e(TAG,
					"*** WARNING *** surfaceCreated() gave us a null surface!");
		}
		if (!hasSurface) {
			hasSurface = true;
			initCamera(holder);
		}
	}

	@Override
	public void surfaceChanged(SurfaceHolder arg0, int arg1, int arg2, int arg3) {

	}

	@Override
	public void surfaceDestroyed(SurfaceHolder arg0) {
		hasSurface = false;
	}

	private void initCamera(SurfaceHolder surfaceHolder) {
		if (surfaceHolder == null) {
			throw new IllegalStateException("No SurfaceHolder provided");
		}
		if (cameraManager.isOpen()) {
			Log.w(TAG,
					"initCamera() while already open -- late SurfaceView callback?");
			return;
		}
		try {
			cameraManager.openDriver(surfaceHolder);
			// Creating the handler starts the preview, which can also throw a
			// RuntimeException.
			if (handler == null) {
				handler = new CaptureActivityHandler(this, decodeFormats,
						decodeHints, characterSet, cameraManager);
			}
		} catch (IOException ioe) {
			Log.w(TAG, ioe);
			displayFrameworkBugMessageAndExit(ioe.getMessage());
		} catch (RuntimeException e) {
			// Barcode Scanner has seen crashes in the wild of this variety:
			// java.?lang.?RuntimeException: Fail to connect to camera service
			Log.w(TAG, "Unexpected error initializing camera", e);
			displayFrameworkBugMessageAndExit("Unexpected error initializing camera: "
					+ e.getMessage());
		}
	}

	private void displayFrameworkBugMessageAndExit(String msg) {
		AlertDialog.Builder builder = new AlertDialog.Builder(this);
		builder.setTitle(getString(R.string.app_name));
		builder.setMessage(msg);
		builder.setPositiveButton(R.string.button_ok, new FinishListener(this));
		builder.setOnCancelListener(new FinishListener(this));
		builder.show();
	}

	/**
	 * A valid barcode has been found, so give an indication of success and show
	 * the results.
	 * 
	 * @param rawResult
	 *            The contents of the barcode.
	 * @param scaleFactor
	 *            amount by which thumbnail was scaled
	 * @param barcode
	 *            A greyscale bitmap of the camera data which was decoded.
	 */
	public void handleDecode(Result rawResult, Bitmap barcode, float scaleFactor) {
		pd = ProgressDialog.show(Scanner.this,
				getResources().getString(R.string.loading_scanner_title),
				getResources().getString(R.string.loading_scanner_description),
				true, false, null);

		boundService.sendMessage("u_id=" + user_id + "&code=" + rawResult);

		// Wait a moment or else it will scan the same barcode continuously
		// about 3 times
		if (handler != null) {
			handler.sendEmptyMessageDelayed(R.id.restart_preview, 1000L);
		}
	}

	public boolean onKeyUp(int keyCode, KeyEvent event) {
		switch (keyCode) {
			case KeyEvent.KEYCODE_MENU: {
				mSystemUiHider.toggle();
				return true;
			}
			case KeyEvent.KEYCODE_BACK: {
				if (mSystemUiHider.isVisible()) {
					mSystemUiHider.hide();
					return true;
				}
			}
		}
		return super.onKeyUp(keyCode, event);
	}

	public void onToggleClicked(View v) {
		Boolean state = ((ToggleButton) v).isChecked();

		switch (v.getId()) {
			case R.id.toggle_flash:
				cameraManager.setTorch(state);
			break;
			case R.id.toggle_focus:
				cameraManager.setFcous(state);
			break;
		}
	}

	public void setupSpinner() {
		pd = ProgressDialog.show(Scanner.this,
				getResources().getString(R.string.loading_databases_title),
				getResources()
						.getString(R.string.loading_databases_description),
				true, false, null);

		boundService.sendMessage("u_id=" + user_id);
	}

	public void setupSpinner(String databases) {
		String dbs[] = databases.split("\\|\\|");

		dbs_id = new int[dbs.length];

		for (int i = 0; i < dbs.length; i++) {
			String split[] = dbs[i].split("\\=");

			dbs_id[i] = Integer.parseInt(split[1]);
			dbs[i] = split[0];
		}

		ArrayAdapter<String> spinnerArrayAdapter = new ArrayAdapter<String>(
				this, android.R.layout.simple_spinner_item, dbs);
		spinnerArrayAdapter
				.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
		spinner_databases.setAdapter(spinnerArrayAdapter);

		pd.dismiss();
	}

	public void setDatabase(int id) {
		database_id = id;
	}

	private BroadcastReceiver socketServiceResponseReceiver = new BroadcastReceiver() {
		@Override
		public void onReceive(Context context, Intent intent) {
			String action = intent.getStringExtra("action");
			String message = intent.getStringExtra("message");

			Log.i("debug", action + "|" + message);

			if (action.equals("status")) {
				if (message.equals("online")) {
				} else {
					finish();
				}
			} else if (action.equals("response")) {
				if (message.indexOf("ok") == 0) {
					// viewfinderView.setMaskColor(getResources().getColor(R.color.viewfinder_mask_green));
					//
					// Runnable task = new Runnable() {
					// @Override
					// public void run() {
					// viewfinderView.setMaskColor(getResources().getColor(R.color.viewfinder_mask_red));
					// }
					// };
					// handler.postDelayed(task, 2000L);
				} else {
					AlertDialog alertDialog = new AlertDialog.Builder(
							Scanner.this).create();

					alertDialog.setTitle(getResources().getText(
							R.string.dialog_alert_title));
					alertDialog.setMessage(message);
					alertDialog.setButton(RESULT_OK,
							getResources().getText(R.string.button_ok),
							new DialogInterface.OnClickListener() {
								@Override
								public void onClick(DialogInterface dialog,
										int which) {
									// TODO Auto-generated method stub
								}
							});
					alertDialog.show();
				}
				pd.dismiss();
			} else if (action.equals("databases")) {
				setupSpinner(message);
			}
		}
	};

	private ServiceConnection service_connection = new ServiceConnection() {
		@Override
		public void onServiceConnected(ComponentName name, IBinder service) {
			boundService = ((SocketService.LocalBinder) service).getService();
			setupSpinner();
		}

		@Override
		public void onServiceDisconnected(ComponentName name) {
			boundService = null;
		}

	};

	private void doBindService() {
		if (!isServiceBound) {
			LocalBroadcastManager.getInstance(this).registerReceiver(
					socketServiceResponseReceiver,
					new IntentFilter("SocketServiceResponse"));
			bindService(new Intent(Scanner.this, SocketService.class),
					service_connection, Context.BIND_AUTO_CREATE);
			isServiceBound = true;
		}
	}

	private void doUnbindService() {
		if (isServiceBound) {
			LocalBroadcastManager.getInstance(this).unregisterReceiver(
					socketServiceResponseReceiver);
			unbindService(service_connection);
			isServiceBound = false;
		}
	}

	@Override
	protected void onDestroy() {
		doUnbindService();
		super.onDestroy();
	}

	@Override
	public void onItemSelected(AdapterView<?> parent, View view, int pos,
			long id) {
		setDatabase(dbs_id[pos]);
	}

	@Override
	public void onNothingSelected(AdapterView<?> arg0) {
		// TODO Auto-generated method stub
	}
}
