package com.placella.socialconnections;

import java.io.BufferedOutputStream;
import java.io.FileOutputStream;
import android.content.Context;
import android.graphics.*;
import android.graphics.Bitmap.CompressFormat;
import android.media.FaceDetector;
import android.media.FaceDetector.Face;

public class FacialRecognition {
	private Context context; 
	FacialRecognition(Context context) {
		this.context = context;
	}
	public int detect(String filename) {
	    BitmapFactory.Options bitmapFactoryOptions = new BitmapFactory.Options();
	    bitmapFactoryOptions.inPreferredConfig = Bitmap.Config.RGB_565;
	    Bitmap bitmap = BitmapFactory.decodeFile(
	    	filename,
	    	bitmapFactoryOptions
	    );
	    FaceDetector fd = new FaceDetector(
	    	bitmap.getWidth(),
    		bitmap.getHeight(),
    		20
	    );
	    Face[] detectedFaces = new FaceDetector.Face[100];
	    int numFaces = fd.findFaces(bitmap, detectedFaces);
	    for(int count=0; count<numFaces; count++) {
		    Face face = detectedFaces[count];
		    PointF midPoint = new PointF();
		    face.getMidPoint(midPoint);
		    float eyeDistance = face.eyesDistance();
		    eyeDistance *= 1.5;
		    System.out.println(
		    	"Face " + count + ": " + 
	    		(midPoint.x-eyeDistance) + "," +
	    		(midPoint.y-eyeDistance) + "," +
	    		(midPoint.x+eyeDistance) + "," +
	    		(midPoint.y+eyeDistance)
		    );
		    
		    Matrix matrix = new Matrix();
		    matrix.postScale(1, 1);
		    Bitmap facePic = Bitmap.createBitmap(
		    	bitmap,
		    	(int)(midPoint.x-eyeDistance),
		    	(int)(midPoint.y-eyeDistance),
		    	(int)((midPoint.x+eyeDistance)-(midPoint.x-eyeDistance)),
		    	(int)((midPoint.y+eyeDistance)-(midPoint.y-eyeDistance)),
		    	matrix,
		    	true
		    );
		    bitmap2file(
		    	facePic,
		    	"face" + count + ".jpg"
		    );
	    }
	    return numFaces;
	}
	
	private void bitmap2file(Bitmap bitmap, String filename) {
		try {
			FileOutputStream fos = context.openFileOutput(filename, Context.MODE_PRIVATE);
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
