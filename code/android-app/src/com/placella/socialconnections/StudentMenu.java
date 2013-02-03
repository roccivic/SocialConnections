package com.placella.socialconnections;

import android.os.Bundle;
import android.app.Activity;
import android.content.*;
import android.view.*;
import android.webkit.*;
import android.widget.Button;

public class StudentMenu extends Activity {
	Activity self = this;
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_student_menu);
        Intent incoming = getIntent();
        final String token = incoming.getStringExtra("token");
        
        Button button;
        
        button = (Button) findViewById(R.id.makeExcuseBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "makeExcuse", token);
			}
        });
        button = (Button) findViewById(R.id.viewResultsBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "viewResults", token);
			}
        });
        button = (Button) findViewById(R.id.checkAttendanceBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "checkAttendance", token);
			}
        });
        
        button = (Button) findViewById(R.id.logOutBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
			    CookieSyncManager.createInstance(self);
			    CookieManager.getInstance().removeAllCookie();
			    finish();
			}
        });
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_student_menu, menu);
        return true;
    }
    
    /**
     * Called when a launched activity exits, giving the requestCode we started it with,
     * the resultCode it returned, and any additional data from it.
     * @override
     */
    protected void onActivityResult(int requestCode, int resultCode, Intent intent) {
    	if (resultCode == 0) {
    		finish();
    	}
    }
}
