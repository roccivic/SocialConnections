package com.placella.socialconnections;

import java.io.File;

import android.content.DialogInterface;
import android.content.DialogInterface.OnClickListener;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;

/**
 * This activity is used to add a picture
 * to the profile of a student
 */
public class Activity_PicUpload extends CallbackActivity {
	private CallbackActivity self = this;
	private final int CAMERA_REQUEST = 1;
	private String path = Environment.getExternalStorageDirectory().getPath() + "/DCIM/Camera/uploadtest.jpg";
	private String facePath = "data/data/com.placella.socialconnections/files/face0.jpg";
	private boolean haveFace = false;

	/**
	 * Called when the activity is starting.
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_pic_upload);
		
		final EditText reference_id = (EditText) findViewById(R.id.referenceET);
		
		Button button = (Button) findViewById(R.id.BackBtn);
		button.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				finish();
			}
		});
		button = (Button) findViewById(R.id.UploadBtn);
		button.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				String id = reference_id.getText().toString();
				if(id.length() < 1) {
					new Dialog(self, R.string.formError).show();
				} else if (! haveFace) {
					new Dialog(self, R.string.noPicture).show();
				} else {
					FacialRecognition f = new FacialRecognition(self);
					self.showOverlay();
					f.upload(facePath, id);
				}
			}
		});
		
		Button addBtn = (Button) findViewById(R.id.addBtn);
		addBtn.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				Uri uri = Uri.fromFile(new File(path));
				Intent intent = new Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE);
				intent.putExtra(MediaStore.EXTRA_OUTPUT, uri);
				startActivityForResult(intent, CAMERA_REQUEST);
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
    		updateFace();
    	}
    }
    /**
     * Called when the camera activity has finished
     * Updates the preview of the picture to be submitted
     */
    private void updateFace()
    {
    	FacialRecognition f = new FacialRecognition(this);
    	int numFaces = f.detect(path);
    	if (numFaces < 1) {
			haveFace = false;
    		new Dialog(this, R.string.noFaces).show();
    	} else if (numFaces > 1) {
			haveFace = false;
    		new Dialog(this, R.string.tooManyFaces).show();
    	} else {
			haveFace = true;
			BitmapFactory.Options options = new BitmapFactory.Options();
			options.inSampleSize = 2;
			Bitmap bm = BitmapFactory.decodeFile(facePath, options);
			ImageView iv = (ImageView) findViewById(R.id.picture);
			iv.setImageBitmap(bm);
    	}
    }
    /**
     * Initialize the contents of the Activity's standard options menu.
     */
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.activity_pic_upload, menu);
		return true;
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
			new Dialog(self, messages[0], new OnClickListener() {
				@Override
				public void onClick(DialogInterface dialog, int which) {
					self.finish();
				}
			}).show();
    	} else {
			new Dialog(self, messages[0]).show();
    	}
    }
}
