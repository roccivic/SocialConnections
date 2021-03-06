package com.placella.socialconnections;

import java.util.ArrayList;
import java.util.List;

import android.net.Uri;
import android.os.Bundle;
import android.app.Activity;
import android.content.Intent;
import android.view.*;
import android.widget.*;
import android.widget.AdapterView.OnItemClickListener;

public class Activity_LecturerMenu extends Activity {
	private Activity self = this;
	public static final int WEB_REQUEST = 0;
	private String token;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);
        
        token = getIntent().getStringExtra("token");
        final List<ListMenuItem> menu = new ArrayList<ListMenuItem>();
        menu.add(new ListMenuItem(self, R.string.home, "", R.drawable.ic_home));
        menu.add(new ListMenuItem(self, R.string.manageAssessments, "manageAssessments", R.drawable.ic_assessments));
		menu.add(new ListMenuItem(self, R.string.manageGroups, "manageGroups", R.drawable.ic_groups));
		menu.add(new ListMenuItem(self, R.string.manageStudents, "manageStudents", R.drawable.ic_students));
		menu.add(new ListMenuItem(self, R.string.postNotes, "postNotes", R.drawable.ic_dropbox));
		menu.add(new ListMenuItem(self, R.string.twitter, "twitter", R.drawable.ic_twitter));
		menu.add(new ListMenuItem(self, R.string.takeAttendance, "", R.drawable.ic_attendance));
		menu.add(new ListMenuItem(self, R.string.viewExcuses, "viewExcuses", R.drawable.ic_excuses));
		menu.add(new ListMenuItem(self, R.string.viewStudentAttendance, "viewStudentAttendance", R.drawable.ic_attendance));
		
        MenuArrayAdapter adapter = new MenuArrayAdapter(
    		this,
    		android.R.layout.simple_list_item_1,
    		menu
        );
        ListView list = (ListView) findViewById(R.id.menuList);
        list.setAdapter(adapter);
        list.setOnItemClickListener(new OnItemClickListener() {
			@Override
			public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
				if (! menu.get(position).getLink().equals("")) {
					Activity_Web.launch(self, menu.get(position).getLink(), token);
				} else if (position == 6) {
					Intent i = new Intent(self, Activity_TakeAttendance.class);
					i.putExtra("token", token);
		    		startActivityForResult(i, 99);
				} else {
				    finish();
				}
			}
		});
        setResult(1);
    }
    
    protected void onActivityResult(int requestCode, int resultCode, Intent data) 
    {  
    	if (requestCode == WEB_REQUEST && resultCode == 0) {
    		setResult(0);
    		finish();
    	} else if (requestCode == 99 && resultCode == 0) {
    		setResult(0);
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
