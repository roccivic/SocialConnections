package com.placella.socialconnections;

import java.util.ArrayList;
import java.util.List;

import android.net.Uri;
import android.os.Bundle;
import android.app.Activity;
import android.content.*;
import android.view.*;
import android.widget.*;
import android.widget.AdapterView.OnItemClickListener;

public class Activity_StudentMenu extends Activity {
	Activity self = this;
	private String token;
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);
        Intent incoming = getIntent();
        token = incoming.getStringExtra("token");

        final List<ListMenuItem> menu = new ArrayList<ListMenuItem>();
        menu.add(new ListMenuItem(self, R.string.home, "", R.drawable.ic_home));
        menu.add(new ListMenuItem(self, R.string.checkAttendance, "checkAttendance", R.drawable.ic_attendance));
        menu.add(new ListMenuItem(self, R.string.makeExcuse, "makeExcuse", R.drawable.ic_excuse));
        menu.add(new ListMenuItem(self, R.string.notes, "notes", R.drawable.ic_dropbox));
        menu.add(new ListMenuItem(self, R.string.twitter, "twitter", R.drawable.ic_twitter));
        menu.add(new ListMenuItem(self, R.string.viewResults, "viewResults", R.drawable.ic_assessments));
    		
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
				} else {
				    finish();
				}
			}
		});
        setResult(1);
    }
    
    /**
     * Called when a launched activity exits, giving the requestCode we started it with,
     * the resultCode it returned, and any additional data from it.
     * @override
     */
    protected void onActivityResult(int requestCode, int resultCode, Intent intent) {
    	if (resultCode == 0) {
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
