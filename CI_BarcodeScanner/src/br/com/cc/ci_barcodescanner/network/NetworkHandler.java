/*
 * Copyright (C) 2010 ZXing authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package br.com.cc.ci_barcodescanner.network;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.widget.Toast;
import br.com.cc.ci_barcodescanner.R;

public class NetworkHandler {
	
	private ConnectivityManager connMgr;
	private Context context;

	public NetworkHandler(Context c) {
		context = c;
		connMgr = (ConnectivityManager) ((Context) context).getSystemService(Context.CONNECTIVITY_SERVICE);
	}
	
	public Boolean isNetworkAvailable() {
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo != null && networkInfo.isConnected()) {
			return true;
		} else {
			Toast.makeText(context, context.getResources().getString(R.string.no_network_message), Toast.LENGTH_LONG).show();
			return false;
		}
	}
}
