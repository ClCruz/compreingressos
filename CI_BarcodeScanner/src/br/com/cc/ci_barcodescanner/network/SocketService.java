package br.com.cc.ci_barcodescanner.network;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.net.InetAddress;
import java.net.Socket;

import android.app.Service;
import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.support.v4.content.LocalBroadcastManager;
import android.util.Log;

public class SocketService extends Service {
	public static final String SERVERIP = "192.168.1.101";
	public static final int SERVERPORT = 21;
	PrintWriter out;
	BufferedReader in;
	Socket socket;
	InetAddress serverAddr;
	private String last_response;
	private final IBinder myBinder = new LocalBinder();

	@Override
	public IBinder onBind(Intent intent) {
		return myBinder;
	}

	public class LocalBinder extends Binder {
		public SocketService getService() {
			return SocketService.this;
		}
	}

	@Override
	public void onCreate() {
		super.onCreate();

		out = null;
		in = null;
	}

	public void sendMessage(String message) {
		if (out != null && !out.checkError()) {
			out.println(message);
			out.flush();
		}
	}

	public String getLastResponse() {
		if (in != null) {
			return last_response;
		}

		return null;
	}

	public Boolean isConnected() {
		return socket != null && socket.isConnected()
				&& !socket.isInputShutdown() && !socket.isOutputShutdown();
	}

	@Override
	public int onStartCommand(Intent intent, int flags, int startId) {
		super.onStartCommand(intent, flags, startId);

		Runnable connect = new connectSocket();
		new Thread(connect).start();

		Log.i("debug", "Socket Started");

		return START_STICKY;
	}

	class connectSocket implements Runnable {
		@Override
		public void run() {
			try {
				serverAddr = InetAddress.getByName(SERVERIP);
				socket = new Socket(serverAddr, SERVERPORT);

				out = new PrintWriter(new BufferedWriter(
						new OutputStreamWriter(socket.getOutputStream())), true);
				in = new BufferedReader(new InputStreamReader(
						socket.getInputStream()));

				Runnable status = new connectionStatus();
				new Thread(status).start();

				while (true) {
					last_response = in.readLine();

					if (last_response != null) {
						Intent intent = new Intent("SocketServiceResponse");
						
						if (last_response.indexOf("response") == 0) {
							intent.putExtra("action", "response");
							last_response = last_response.substring(8);
						} else if (last_response.indexOf("databases") == 0) {
							intent.putExtra("action", "databases");
							last_response = last_response.substring(9);
						}

						intent.putExtra("message", last_response);
						
						LocalBroadcastManager.getInstance(SocketService.this)
								.sendBroadcast(intent);
					} else {
						Thread.sleep(5000);
					}
				}
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}

	class connectionStatus implements Runnable {
		@Override
		public void run() {
			long time = 500;
			String last_status = "";
			String new_status;

			while (true) {
				if (isConnected()) {
					new_status = "online";
					time = 15000;
				} else {
					new_status = "offline";
					time = 5000;
				}

				if (!last_status.equals(new_status)) {
					Intent intent = new Intent("SocketServiceResponse");
					intent.putExtra("action", "status");
					intent.putExtra("message", new_status);
					LocalBroadcastManager.getInstance(SocketService.this)
							.sendBroadcast(intent);
					last_status = new_status;
				}

				try {
					Thread.sleep(time);
				} catch (InterruptedException e) {
					e.printStackTrace();
				}
			}
		}
	}

	@Override
	public void onDestroy() {
		try {
			socket.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		socket = null;

		super.onDestroy();
	}

}