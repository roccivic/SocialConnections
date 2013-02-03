package com.placella.socialconnections;

import java.io.File;
import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;
import java.text.SimpleDateFormat;
import java.util.Date;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.ResponseHandler;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.BasicResponseHandler;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.cookie.DateUtils;
import android.annotation.SuppressLint;


public class Upload {
	private static String bucket = "4f3facab-f804-4b4a-a73b-6e5ae33b07ea";
	private static String secretKey ="aT4pL3nsDlU92DhCDJGXPYlsg1ENnYpao75jY2hb";
private static String urlpath = "https://upload-api.kooaba.com/api/v4/buckets/"+bucket+"/items";


@SuppressLint("SimpleDateFormat")
private SimpleDateFormat formatter= new SimpleDateFormat("yyyy/MMM/dd HH:mm:ss");
private static String path="";
private static String response;
private static String name;
private static String reference_id;
	public Upload(String picPath, String nameIn, String reference_idIn)  {
	path=picPath;
	name=nameIn;
	reference_id = reference_idIn;
	new Thread(
			new Runnable() {
				public void run() {
					Upload.uploadItem();
				}
			}
		).start();
}

	private static void uploadItem()
	{
		try {
			
			URI url = new URI(urlpath);
			HttpClient client = new DefaultHttpClient();
			HttpPost post = new HttpPost(url);
			File file = new File(path);
			FileBody imagePart = new FileBody(file, "image/jpeg");
			MultipartEntity pairs = new MultipartEntity();
			pairs.addPart("image", imagePart);
			pairs.addPart("title", new StringBody(name));
			pairs.addPart("reference_id",new StringBody(reference_id));
			
			//pairs.addPart("query[bounding_box]", boundingBoxPart);
			ResponseHandler<String> responseHandler = new BasicResponseHandler();
			post.setEntity(pairs);
			post.addHeader("Date", DateUtils.formatDate(new Date()).replaceFirst("[+]00:00$", ""));
			post.addHeader("Authorization", "Token " + secretKey);
			response = client.execute(post,responseHandler);
			System.out.println(response);
			
			
	}
	catch (URISyntaxException eUri) 
	{
		eUri.printStackTrace();
		
	}	
	catch (ClientProtocolException CPE) 
	{
		
		CPE.printStackTrace();
	} 
	catch (IOException IOe) 
	{
		IOe.printStackTrace();
	
	}
	catch(Exception e) 
	{
		e.printStackTrace();
		
	}
	}

}
