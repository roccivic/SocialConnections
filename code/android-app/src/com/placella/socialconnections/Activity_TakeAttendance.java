package com.placella.socialconnections;

import java.io.File;
import java.util.ArrayList;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

/**
 * This is activity is used by the lecurer
 * to take the attendance of a group by
 * using facial recognition, and by using
 * a fallback method if the facial recognition
 * fails.
 */
public class Activity_TakeAttendance extends CallbackActivity {
	private String path = Environment.getExternalStorageDirectory().getPath() + "/DCIM/Camera/test.jpg";
	private String facePath = "data/data/com.placella.socialconnections/files/";
	private final int CAMERA_REQUEST = 1;
	private CallbackActivity self = this;
	private int detectedFaces = 0;
	private int numFaces = 0;
	private int pictures = 0;
	private ArrayList<String> student_ids = new ArrayList<String>();

	/**
	 * Called when the activity is starting.
	 */
	@Override
	public void onCreate(Bundle savedInstanceState)
	{
	    super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_take_attendance);

		Button button = (Button) findViewById(R.id.back);
		button.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				finish();
			}
		});
		button = (Button) findViewById(R.id.add);
		button.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				Uri uri = Uri.fromFile(new File(path));
				Intent intent = new Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE);
				intent.putExtra(MediaStore.EXTRA_OUTPUT, uri);
				startActivityForResult(intent, CAMERA_REQUEST);
			}
		});
		button = (Button) findViewById(R.id.ok);
		button.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				if (pictures == 0) {
					new Dialog(self, R.string.noPicture).show();
				} else if (student_ids.size() < 1) {
					new Dialog(self, R.string.noValidFaces).show();
				} else {
					new Dialog(self, "FIXME: FUNCTIONALITY NOT IMPLEMENTED\nshould show the fallback list with checkboxes now").show();
				}
			}
		});
	}
	/**
	 * Called when an activity you launched exits, giving you the requestCode you
	 * started it with, the resultCode it returned, and any additional data from it.
	 * The resultCode will be RESULT_CANCELED if the activity explicitly returned that,
	 * didn't return any result, or crashed during its operation.
	 */
    protected void onActivityResult(int requestCode, int resultCode, Intent data) 
    {  
    	if (requestCode == CAMERA_REQUEST && resultCode == RESULT_OK) {
    		detectFaces();
    	}
    }
    /**
     * Detects and recognises the faces in a picture thaken with the camera
     */
    private void detectFaces()
    {
    	FacialRecognition f = new FacialRecognition(self);
    	numFaces = f.detect(path, detectedFaces);
    	if (numFaces < 1) {
    		new Dialog(this, R.string.noFaces).show();
    	} else {
			pictures++;
			self.showOverlay();
			System.out.println("Query: " + numFaces + "," + detectedFaces);
			f.query(facePath, numFaces, detectedFaces);
			detectedFaces += numFaces;
    	}
    }
	/**
	 * Called when the FacialRecognition class has finished
	 * processing the images submitted to it.
	 *
	 * @param success  Whether the facial recognition was successful
	 * @param messages A list of messages. In case of an error there will
	 *                 be only one message, the reason of the failure.
	 *                 In case of a success, there may be no messages at all,
	 *                 if the request was for uploading a picture. If, however,
	 *                 the request was for recognising the faces in an image, the
	 *                 messages will contain a list of reference IDs of the faces
	 *                 that were recognised
	 */
    public void callback(boolean success, String[] messages) {
		self.hideOverlay();
    	if (success) {
			TextView tv = (TextView) findViewById(R.id.status);
			String prefix = "";
			if (detectedFaces > numFaces) {
				prefix = tv.getText().toString() + "\n";
			}
			tv.setText(
				prefix + String.format(
					getString(R.string.status),
					messages.length,
					numFaces,
					pictures
				)
			);
			for (String s : messages) {
				student_ids.add(s);
			}
    	} else {
			new Dialog(self, messages[0]).show();
    	}
    }
}
