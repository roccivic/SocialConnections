package com.placella.socialconnections;

import java.io.File;
import java.io.IOException;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.app.Activity;
import android.content.Intent;
import android.view.Menu;
import android.view.View;
import android.widget.Button;

public class LecturerMenu extends Activity {
	
	private static final int CAMERA_REQUEST = 0;
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
      }
    
    protected void onActivityResult(int requestCode, int resultCode, Intent data) 
    {  
      
    	if (requestCode == CAMERA_REQUEST && resultCode == RESULT_OK) {  
        	//run query
        	Query q = new Query(path);
    	}
      }  
        
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_lecturer_menu, menu);
        return true;
    }
}
