package br.com.cc.ci_barcodescanner.activity;

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
import android.os.Bundle;
import android.os.IBinder;
import android.support.v4.content.LocalBroadcastManager;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import br.com.cc.ci_barcodescanner.R;
import br.com.cc.ci_barcodescanner.network.NetworkHandler;
import br.com.cc.ci_barcodescanner.network.SocketService;

public final class Login extends Activity {

	public static NetworkHandler network;

	private boolean isServiceBound;
	private SocketService boundService;

	Button auth_button;
	ProgressDialog pd;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		setContentView(R.layout.activity_login);
		startService(new Intent(Login.this, SocketService.class));

		auth_button = (Button) findViewById(R.id.button_login);

		network = new NetworkHandler(this);
	}

	@Override
	protected void onResume() {
		super.onResume();
		if (network.isNetworkAvailable())
			doBindService();
		else
			auth_button.setEnabled(false);
	}

	@Override
	protected void onPause() {
		doUnbindService();
		super.onPause();
	}

	public void authenticate(View v) {
		EditText user = (EditText) findViewById(R.id.text_login);
		EditText password = (EditText) findViewById(R.id.text_password);

		pd = ProgressDialog.show(Login.this,
				getResources().getString(R.string.loading_auth_title),
				getResources().getString(R.string.loading_auth_description),
				true, false, null);

		boundService.sendMessage("u=" + user.getText().toString() + "&p="
				+ password.getText().toString());
	}

	private BroadcastReceiver socketServiceResponseReceiver = new BroadcastReceiver() {
		@Override
		public void onReceive(Context context, Intent intent) {
			String action = intent.getStringExtra("action");
			String message = intent.getStringExtra("message");
			
			Log.i("debug", action + "|" + message);

			if (action.equals("response")) {
				if (message.indexOf("id=") == 0) {
					int user_id = Integer.parseInt(message.substring(3)
							.toString());

					Intent intent2 = new Intent(Login.this, Scanner.class);
					intent2.putExtra("user_id", user_id);
					startActivity(intent2);
				} else {
					AlertDialog alertDialog = new AlertDialog.Builder(
							Login.this).create();

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
			} else if (action.equals("status")) {
				if (message.equals("online")) {
					auth_button.setEnabled(true);
				} else {
					auth_button.setEnabled(false);
				}
			}
		}
	};

	private ServiceConnection service_connection = new ServiceConnection() {
		// EDITED PART
		@Override
		public void onServiceConnected(ComponentName name, IBinder service) {
			// TODO Auto-generated method stub
			boundService = ((SocketService.LocalBinder) service).getService();
		}

		@Override
		public void onServiceDisconnected(ComponentName name) {
			// TODO Auto-generated method stub
			boundService = null;
		}

	};

	private void doBindService() {
		if (!isServiceBound) {
			LocalBroadcastManager.getInstance(this).registerReceiver(
					socketServiceResponseReceiver,
					new IntentFilter("SocketServiceResponse"));
			bindService(new Intent(Login.this, SocketService.class),
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
		stopService(new Intent(Login.this, SocketService.class));
		super.onDestroy();
	}
}
