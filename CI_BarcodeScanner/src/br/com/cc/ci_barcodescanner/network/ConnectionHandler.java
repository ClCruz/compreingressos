package br.com.cc.ci_barcodescanner.network;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.InetSocketAddress;
import java.net.Socket;
import java.net.SocketAddress;

import android.app.Service;
import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

public class ConnectionHandler extends Service {

	public static String serverip = "192.168.1.101";
	public static int serverport = 7777;
	private static Socket socket;
	private BufferedWriter out;
	private BufferedReader in;

	public String sendMessageGetResponse(String message) {
		try {
			out = new BufferedWriter(new OutputStreamWriter(
					socket.getOutputStream()));
			in = new BufferedReader(new InputStreamReader(
					socket.getInputStream()));
		} catch (IOException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		}

		try {
			out.write(message);
			out.flush();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		try {
			return in.readLine();
		} catch (IOException e) {
			Log.i("debug", "- Cannot get Response: " + e.getMessage());
			e.printStackTrace();
		}

		return null;
	}

	public void close() {
		if (socket.isConnected()) {
			try {
				socket.close();
			} catch (Exception e) {
				Log.i("debug", "- Cannot close Socket: " + e.getMessage());
				e.printStackTrace();
			}
		}
	}

	@Override
	public IBinder onBind(Intent intent) {
		// TODO Auto-generated method stub
		return null;
	}

	public class LocalBinder extends Binder {
		public ConnectionHandler getService() {
			return ConnectionHandler.this;
		}
	}

	@Override
	public void onCreate() {
		super.onCreate();

		try {
			Log.i("debug", "- Creating Socket");
			socket = new Socket();
		} catch (Exception e) {
			Log.i("debug", "- Cannot create Socket: " + e.getMessage());
			e.printStackTrace();
		}

		Runnable connect = new connectSocket();
		new Thread(connect).start();
	}

	class connectSocket implements Runnable {

		@Override
		public void run() {
			SocketAddress socketAddress = new InetSocketAddress(serverip,
					serverport);
			try {
				socket.connect(socketAddress);
			} catch (IOException e) {
				e.printStackTrace();
			}

		}

	}

	@Override
	public void onDestroy() {
		super.onDestroy();
		close();
	}

}