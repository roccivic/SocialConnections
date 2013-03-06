package com.placella.socialconnections;

import android.net.Uri;
import android.os.Bundle;
import android.app.Activity;
import android.content.Intent;
import android.view.*;
import android.webkit.*;
import android.widget.*;
import android.widget.AdapterView.OnItemClickListener;

public class Activity_LecturerMenu extends Activity {
	private Activity self = this;
	public static final int WEB_REQUEST = 0;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);
        
        final String token = getIntent().getStringExtra("token");
        final ListMenuItem[] menu = new ListMenuItem[] {
        	new ListMenuItem(self, R.string.manageAssessments, "manageAssessments"),
        	new ListMenuItem(self, R.string.manageGroups, "manageGroups"),
        	new ListMenuItem(self, R.string.manageStudents, "manageStudents"),
        	new ListMenuItem(self, R.string.postNotes, "postNotes"),
        	new ListMenuItem(self, R.string.twitter, "twitter"),
        	new ListMenuItem(self, R.string.takeAttendance, ""),
        	new ListMenuItem(self, R.string.viewExcuses, "viewExcuses"),
        	new ListMenuItem(self, R.string.viewStudentAttendance, "viewStudentAttendance"),
        	new ListMenuItem(self, R.string.logOut, "")
        };
        ArrayAdapter<ListMenuItem> adapter = new ArrayAdapter<ListMenuItem>(
    		this,
    		android.R.layout.simple_list_item_1,
    		menu
        );
        ListView list = (ListView) findViewById(R.id.menuList);
        list.setAdapter(adapter);
        list.setOnItemClickListener(new OnItemClickListener() {
			@Override
			public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
				if (! menu[position].getLink().equals("")) {
					Activity_Web.launch(self, menu[position].getLink(), token);
				} else if (position == 5) {
					Intent i = new Intent(self, Activity_TakeAttendance.class);
					i.putExtra("token", token);
		    		startActivityForResult(i, 99);
				} else {
				    CookieSyncManager.createInstance(self);
				    CookieManager.getInstance().removeAllCookie();
				    finish();
				}
			}
		});
    }
    
    protected void onActivityResult(int requestCode, int resultCode, Intent data) 
    {  
    	if (requestCode == WEB_REQUEST && resultCode == 0) {
    		finish();
    	} else if (requestCode == 99 && resultCode == 1) {
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
