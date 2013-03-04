package com.placella.socialconnections;

import android.net.Uri;
import android.os.Bundle;
import android.app.Activity;
import android.content.*;
import android.view.*;
import android.webkit.*;
import android.widget.Button;

public class Activity_StudentMenu extends Activity {
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
				Activity_Web.launch(self, "makeExcuse", token);
			}
        });
        button = (Button) findViewById(R.id.viewResultsBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Activity_Web.launch(self, "viewResults", token);
			}
        });
        button = (Button) findViewById(R.id.checkAttendanceBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Activity_Web.launch(self, "checkAttendance", token);
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
        
        button = (Button) findViewById(R.id.notesBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Activity_Web.launch(self, "notes", token);
			}
        });
        button = (Button) findViewById(R.id.twitterBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Activity_Web.launch(self, "twitter", token);
			}
        });
        
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

    /**
     * Creates the menu from XML file
     */
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.menu, menu);
		return true;
	}
	
    /**
	 * Called whenever an item in the options menu is selected.
     *
	 * @param item The menu item that was selected
	 */
	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		if (item.getItemId() == R.id.menu_openbrowser) {
			startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(CONFIG.webUrl)));
        }
        return super.onOptionsItemSelected(item);
    }
}
