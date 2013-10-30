package br.com.cc.ci_barcodescanner.util;

import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.net.URLEncoder;
import java.util.HashMap;
import java.util.Map;

public class MapQuery {
	public static String urlEncodeUTF8(String s) {
		try {
			return URLEncoder.encode(s, "UTF-8");
		} catch (UnsupportedEncodingException e) {
			throw new UnsupportedOperationException(e);
		}
	}

	public static String urlDecodeUTF8(String s) {
		try {
			return URLDecoder.decode(s, "UTF-8");
		} catch (UnsupportedEncodingException e) {
			throw new UnsupportedOperationException(e);
		}
	}

	public static String mapToString(Map<?, ?> map) {
		StringBuilder sb = new StringBuilder();

		for (Map.Entry<?, ?> entry : map.entrySet()) {
			if (sb.length() > 0) {
				sb.append("&");
			}

			sb.append(String.format("%s=%s", urlEncodeUTF8(entry.getKey()
					.toString()), urlEncodeUTF8(entry.getValue().toString())));
		}

		return sb.toString();
	}

	public static Map<String, String> stringToMap(String params) {
		Map<String, String> map = new HashMap<String, String>();

		String entries[] = params.split("\\&");

		for (String entry : entries) {
			String param[] = entry.split("\\=");
			map.put(urlDecodeUTF8(param[0]), urlDecodeUTF8(param[1]));
		}

		return map;
	}
}