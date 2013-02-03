package com.placella.socialconnections;

import java.net.URI;
import java.util.*;

import org.apache.http.*;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;

import android.content.Context;

/**
 * This class is used to perform remote authentication
 * to the web app. Upon successful authentication, it will
 * give us a token, that we can use to launch the WebView
 * in an Activity and it will also tell us what kind of
 * level of access the user has for the web app. 
 */
public class RemoteAuth {
	private static String response = "";
	private static String token = "";
	private static int accessLevel = ACCESSLEVEL.ANONYMOUS;

	/**
	 * Performs the HTTP POST request to establish remote authentication
	 * 
	 * @return boolean success/failure
	 */
	public static boolean login(Context context, String uri, String username, String password) {
		boolean success = true;
		try {
			HttpResponse httpResponse;
			URI url;
			url = new URI(uri);
			HttpClient client = new DefaultHttpClient();
			HttpPost put = new HttpPost(url);
			List<NameValuePair> pairs = new ArrayList<NameValuePair>();
			pairs.add(new BasicNameValuePair("username", username));
			pairs.add(new BasicNameValuePair("password", password));
			put.setEntity(new UrlEncodedFormEntity(pairs));
			httpResponse = client.execute(put);
			int statuscode = httpResponse.getStatusLine().getStatusCode();
			if (statuscode == 200) {
				// login was successful
				HttpEntity responseEntity = httpResponse.getEntity();
				if (responseEntity != null) {
					try {
						// decode response
						response = EntityUtils.toString(responseEntity);
						String[] parts = response.split("\n");
						token = parts[0];
						accessLevel = Integer.parseInt(parts[1]);
						System.out.println(token + ":" + accessLevel);
					} catch (Exception e) {
						success = false;
						response = e.toString();
					}
				} else {
					success = false;
					response = context.getString(R.string.httpResponseError);
				}
			} else if (statuscode == 401) {
				success = false;
				response = context.getString(R.string.error401);
			} else if (statuscode == 429) {
				success = false;
				response = context.getString(R.string.error429);
			} else {
				success = false;
				response = String.format(context.getString(R.string.httpError), statuscode);
			}
		} catch (Exception e) {
			success = false;
			response = e.toString();
		}
		return success;
	}
	/**
	 * Gets the raw response from the server in vase of success or
	 * an error message if the request failed 
	 * 
	 * @return String
	 */
	public static String getResponse() {
		return response;
	}
	/**
	 * In case of a successful authentication returns the token
	 * that can be used (once) to launch the web app
	 * 
	 * @return String
	 */
	public static String getToken() {
		return token;
	}
	/**
	 * In case of a successful authentication this function
	 * can be used to find out what kind of access the user has
	 * on the web app
	 * 
	 * @return a Constant from the ACCESSLEVEL class
	 */
	public static int getAccessLevel() {
		return accessLevel;
	}
}
