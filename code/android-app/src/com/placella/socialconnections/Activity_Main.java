package com.placella.socialconnections;

import android.os.Bundle;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;


public class Activity_Main extends Activity {
	Context self = this;
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
				
		Button logInBtn = (Button) findViewById(R.id.LogInBtn);
		logInBtn.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				new Thread(
					new Runnable() {
						public void run() {
						    EditText username = (EditText) findViewById(R.id.userET);
						    EditText password = (EditText) findViewById(R.id.passET);
						    boolean success = RemoteAuth.login(
						    	self,
						    	VARS.webUrl + "remote.php",
						    	username.getText().toString(),
						    	password.getText().toString()
						    );
						    if (! success) {
						    	new Dialog(self, RemoteAuth.getResponse()).show();
						    } else {
						    	String token = RemoteAuth.getToken();
						        Bundle b = new Bundle();
						        b.putString("token", token);
						        Intent intent;
						    	int accessLevel = RemoteAuth.getAccessLevel();
						    	if (accessLevel == ACCESSLEVEL.STUDENT) {
							        intent = new Intent(self, Activity_StudentMenu.class);
							        intent.putExtras(b);
						    		startActivity(intent);
						    	} else if (accessLevel == ACCESSLEVEL.LECTURER) {
							        intent = new Intent(self, Activity_LecturerMenu.class);
							        intent.putExtras(b);
						    		startActivity(intent);
						    	} else if (accessLevel == ACCESSLEVEL.ADMIN) {
							    	new Dialog(self, R.string.noAdminAccess).show();
						    	} else if (accessLevel == ACCESSLEVEL.SUPER) {
							    	new Dialog(self, R.string.noSuperAccess).show();
						    	} else {
							    	new Dialog(self, R.string.unknowAuthError).show();
						    	}
						    }
						}
					}
				).start();
			}
		});
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.activity_main, menu);
		return true;
	}
}
