package com.placella.socialconnections;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.net.URI;
import java.util.Date;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.cookie.DateUtils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Bitmap.CompressFormat;
import android.graphics.BitmapFactory;
import android.graphics.Matrix;
import android.graphics.PointF;
import android.media.FaceDetector;
import android.media.FaceDetector.Face;

/**
 * This class is used for all facial recognition functionality.
 * It uses Android.FaceDetector for face detection
 * and a third party api for the facial recognition.
 */
public class FacialRecognition {
	private CallbackActivity activity; 
	private final int MAX_FACES = 30;
	
	/**
	 * Constructor
	 * 
	 * @param activity The calling activity
	 */
	FacialRecognition(CallbackActivity activity) {
		this.activity = activity;
	}
	/**
	 * Used to upload a picture
	 * The picture should be already cropped using FacialRecognition.detect()
	 * 
	 * @param filename     The filename of the picture to upload, generated by FacialRecognition.detect()
	 * @param reference_id The identifier to associate with the picture
	 */
	public synchronized void upload(final String session, final String filename, final int numFaces, final int offset) {
		new Thread(
			new Runnable() {
				public void run() {
					for (int i=0; i<numFaces-1; i++) {
						__upload(session, filename + "face" + (offset + i) + ".jpg", false);
					}
					__upload(session, filename + "face" + (offset + numFaces-1) + ".jpg", true);
				}
			}
		).start();
	}
	/**
	 * Called by FacialRecognition.upload() in a thread
	 */
	private void __upload(String session, String filename, boolean isLast) {
		boolean success = false;
		String message = "";
		try {
			URI url = new URI(VARS.webUrl + "upload.php?session=" + session);
			HttpClient client = new DefaultHttpClient();
			HttpPost post = new HttpPost(url);
			File file = new File(filename);
			FileBody imagePart = new FileBody(file, "image/jpeg");
			MultipartEntity pairs = new MultipartEntity();
			pairs.addPart("image", imagePart);
			post.setEntity(pairs);
			post.addHeader("Date", DateUtils.formatDate(new Date()).replaceFirst("[+]00:00$", ""));
			HttpResponse httpResponse = client.execute(post);
			int statuscode = httpResponse.getStatusLine().getStatusCode();
			if (statuscode == 200) {
				success = true;
				message = activity.getString(R.string.uploadOk);
			} else {
				message = String.format(activity.getString(R.string.httpError),statuscode);
			}
		} catch(final Exception e) {
			message = e.getMessage();
		}
		final boolean s = success;
		final String[] m = new String[1];
		m[0] = message;
		if (isLast) {
			activity.runOnUiThread(new Runnable() {
				public void run() {
					activity.callback(s, m);
				}
			});
		}
	}
	/**
	 * Performs face detection on a picture
	 * Saves the detected faces in sequentially numbered files
	 * 
	 * @param  filename A path to a jpeg file to process
	 *
	 * @return the number of detected face
	 */
	public int detect(String filename) {
		return detect(filename, 0);
	}
	/**
	 * Performs face detection on a picture
	 * Saves the detected faces in sequentially numbered files
	 * 
	 * @param filename A path to a jpeg file to process
	 * @param offset   A number to use to start labeling the files
	 *                 the output pictures. Useful if multiple pictures
	 *                 are being used to create a single set of faces
	 * 
	 * @return the number of detected face
	 */
	public int detect(String filename, int offset) {
	    BitmapFactory.Options options = new BitmapFactory.Options();
	    options.inPreferredConfig = Bitmap.Config.RGB_565;
	    Bitmap bitmap = BitmapFactory.decodeFile(
	    	filename,
	    	options
	    );
	    FaceDetector fd = new FaceDetector(
	    	bitmap.getWidth(),
    		bitmap.getHeight(),
    		MAX_FACES
	    );
	    Face[] detectedFaces = new FaceDetector.Face[MAX_FACES];
	    int numFaces = fd.findFaces(bitmap, detectedFaces);
	    for(int count=0; count<numFaces; count++) {
		    Face face = detectedFaces[count];
		    PointF midPoint = new PointF();
		    face.getMidPoint(midPoint);
		    float eyeDistance = face.eyesDistance();
		    double width = eyeDistance * 1.5;
		    double heightTop = eyeDistance * 1.6;
		    double heightBottom = eyeDistance * 2.2;
		    int startX = (int)(midPoint.x-width);
		    if (startX < 0) {
		    	startX = 0;
		    }
		    int startY = (int)(midPoint.y-heightTop);
		    if (startY < 0) {
		    	startY = 0;
		    }
		    int endX = (int)(midPoint.x+width);
		    if (endX > bitmap.getWidth()) {
		    	endX = bitmap.getWidth();
		    }
		    int endY = (int)(midPoint.y+heightBottom);
		    if (endY > bitmap.getHeight()) {
		    	endY = bitmap.getHeight();
		    }
		    Bitmap facePic = Bitmap.createBitmap(
		    	bitmap,
		    	startX,
		    	startY,
		    	endX - startX,
		    	endY - startY,
		    	new Matrix(),
		    	true
		    );
		    bitmap2file(
		    	Bitmap.createScaledBitmap(facePic, 92, 112, false),
		    	"face" + (offset + count) + ".jpg"
		    );
	    }
	    return numFaces;
	}
	/**
	 * Saves a bitmap object as a JPEG file
	 * 
	 * @param bitmap   The input bitmap
	 * @param filename Where to save the resultant file
	 */
	private void bitmap2file(Bitmap bitmap, String filename) {
		try {
			FileOutputStream fos = activity.openFileOutput(filename, Context.MODE_PRIVATE);
	        final BufferedOutputStream bos = new BufferedOutputStream(fos, 4096);
	        bitmap.compress(CompressFormat.JPEG, 95, bos);
	        bos.flush();
	        bos.close();
	        fos.close();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
}
