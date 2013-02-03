package com.placella.socialconnections;

import java.io.File;
import java.io.IOException;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.app.Activity;
import android.content.Intent;
import android.view.*;
import android.webkit.*;
import android.widget.Button;

public class LecturerMenu extends Activity {
	Activity self = this;
	private static final int CAMERA_REQUEST = 0;
	private static final int WEB_REQUEST = 1;
	public String path="";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lecturer_menu);
        
        //declare button for taking attendance
        Button takeAttendance = (Button) this.findViewById(R.id.takeAttendanceBtn);
        //set onclick listener for take attendance button
        takeAttendance.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View arg0) {
				
			//specify the path of the image, create a new file & put path as argument
			path=Environment.getExternalStorageDirectory().getPath() + "/DCIM/Camera/";
			File file = new File(path,"test.jpg");
			path += "test.jpg";
			try {
				file.createNewFile();
				} 
			catch (IOException e) {
				e.printStackTrace();
				}
			Uri uri = Uri.fromFile(file);
			Intent intent = new Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE);
			intent.putExtra(MediaStore.EXTRA_OUTPUT, uri);
			startActivityForResult(intent, CAMERA_REQUEST); 
          
			}	
		});
        
        Intent incoming = getIntent();
        final String token = incoming.getStringExtra("token");
        
        Button button;

        button = (Button) findViewById(R.id.postResultsBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "postResults", token);
			}
        });
        button = (Button) findViewById(R.id.postTwitterBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "postTwitter", token);
			}
        });
        button = (Button) findViewById(R.id.viewTwitterBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "viewTwitter", token);
			}
        });
        button = (Button) findViewById(R.id.viewStudentAttendanceBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "viewStudentAttendance", token);
			}
        });
        button = (Button) findViewById(R.id.postNotesBtn);
        button.setOnClickListener(new Button.OnClickListener () {
			@Override
			public void onClick(View arg0) {
				Web.launch(self, "postNotes", token);
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
    
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
    	if (requestCode == CAMERA_REQUEST && resultCode == RESULT_OK) {
        	//open the attendance activity & specify the path of the file
        	 Intent intent = new Intent(getBaseContext(), Attendance.class);
        	 intent.putExtra("path", path);
        	 startActivity(intent);
    	} else if (requestCode == WEB_REQUEST && resultCode == 0) {
    		finish();
    	}
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_lecturer_menu, menu);
        return true;
    }
}
