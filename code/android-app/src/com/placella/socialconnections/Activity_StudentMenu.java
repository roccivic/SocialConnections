package com.placella.socialconnections;

import android.net.Uri;
import android.os.Bundle;
import android.app.Activity;
import android.content.*;
import android.view.*;
import android.webkit.*;
import android.widget.*;
import android.widget.AdapterView.OnItemClickListener;

public class Activity_StudentMenu extends Activity {
	Activity self = this;
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);
        Intent incoming = getIntent();
        final String token = incoming.getStringExtra("token");

        final ListMenuItem[] menu = new ListMenuItem[] {
    		new ListMenuItem(self, R.string.checkAttendance, "checkAttendance"),
    		new ListMenuItem(self, R.string.makeExcuse, "makeExcuse"),
    		new ListMenuItem(self, R.string.notes, "notes"),
    		new ListMenuItem(self, R.string.twitter, "twitter"),
    		new ListMenuItem(self, R.string.viewResults, "viewResults"),
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
				} else {
				    CookieSyncManager.createInstance(self);
				    CookieManager.getInstance().removeAllCookie();
				    finish();
				}
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
