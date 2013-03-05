package com.placella.socialconnections;

import android.app.Activity;
import android.app.ProgressDialog;

/**
 * Any Activity that uses the FacialRecognition class MUST
 * extend this class. This is necessary because the FacialRecognition
 * class needs to call the callback() method on the calling activity
 * to notify it that it has finished processing the images.
 */
public abstract class CallbackActivity extends Activity {
	/**
	 * Called when the FacialRecognition class has finished
	 * processing the images submitted to it.
	 *
	 * @param success  Whether the facial recognition was successful
	 * @param messages A list of messages. In case of an error there will
	 *                 be only one message, the reason of the failure.
	 *                 In case of a success, there may be no messages at all,
	 *                 if the request was for uploading a picture.
	 */
	public abstract void callback(boolean success, String[] messages);
	/**
	 * A reference to the Progress dialog
	 */
	private ProgressDialog pd;
	/**
	 * Shows the Progress dialog
	 */
	public void showOverlay()
	{
		pd = ProgressDialog.show(this, "", getString(R.string.loading), true);
	}
	/**
	 * Hides the Progress dialog
	 */
	public void hideOverlay()
	{
		pd.dismiss();
	}
	/**
	 * Changes the message in the progress dialog
	 */
	public void setOverlay(String msg)
	{
		pd.setMessage(msg);
	}
}
