package com.placella.socialconnections;

import java.util.Locale;

import android.os.Bundle;
import android.app.Activity;
import android.content.Intent;
import android.view.*;
import android.webkit.*;
import android.widget.Button;

/**
 * Displays a menu and a WebView that points to
 * a selected page from the web-app server.
 */
public class Activity_Web extends Activity {
	private Activity self = this;
	
	/**
	 * Use this method to start this activity
	 *
	 * @param activity The calling activity
	 * @param page     The name of the page to request from the server
	 * @param token    The authentication token. This should have been
	 *                 obtained using the RemoteAuth class.
	 */
	public static void launch(Activity activity, String page, String token)
	{
        Bundle b = new Bundle();
        b.putString("token", token);
        b.putString("page", page);
        Intent intent = new Intent(activity, Activity_Web.class);
        intent.putExtras(b);
        activity.startActivityForResult(intent, 0);
	}
	
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_web);

		Intent intent = getIntent();
		String token = intent.getStringExtra("token");
		String page = intent.getStringExtra("page");
		
		WebView window = (WebView) findViewById(R.id.webView);
		window.getSettings().setJavaScriptEnabled(true);
		window.getSettings().setSavePassword(false);
		window.setWebViewClient(new WebViewClient() {
			@Override
			public boolean shouldOverrideUrlLoading(WebView view, String url)
			{
				// Prevent clicks on links inside the WebView
				// from opening in an external; browser
				return false;
			}
		});
		window.loadUrl(
			VARS.webUrl
			+ "?action=" + page
			+ "&token=" + token
			+ "&mobile=true"
			+ "&lang=" + Locale.getDefault().getLanguage()
		);
		
		Button logOutBtn = (Button) findViewById(R.id.logOutBtn);
		logOutBtn.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				// Destroys user's session
			    CookieSyncManager.createInstance(self);
			    CookieManager.getInstance().removeAllCookie();
			    // Go back to MainActivity
			    setResult(0);
			    finish();
			}
		});
		Button menuBtn = (Button) findViewById(R.id.menuBtn);
		menuBtn.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
			    // Go back to Calling Activity
			    finish();
			}
		});

		// By default, we don't want to go back
		// to MainActivity when the activity dies
		setResult(1);
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_web, menu);
		return true;
	}
}
