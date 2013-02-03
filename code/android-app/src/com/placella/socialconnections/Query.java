package com.placella.socialconnections;

import android.annotation.SuppressLint;
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
import org.apache.http.impl.client.BasicResponseHandler;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.cookie.DateUtils;



/*
 * 
 */
public class Query extends Thread{
	
	private static String secretKey="WT1tbKB0pNPKCwerm3UC39kqBA0HhUGTijYEUlyn";
	private static String urlpath="https://query-api.kooaba.com/v4/query";
	@SuppressLint("SimpleDateFormat")
	private SimpleDateFormat formatter= new SimpleDateFormat("yyyy/MMM/dd HH:mm:ss");
	private static String path="";
	private static String response;
	
	public Query(String picPath)  {
		path=picPath;
		new Thread(
				new Runnable() {
					public void run() {
						Query.makeQuery();
					}
				}
			).start();
	}
	
	 private static void makeQuery() {
		try {
				
				URI url = new URI(urlpath);
				HttpClient client = new DefaultHttpClient();
				HttpPost post = new HttpPost(url);
				File file = new File(path);
				FileBody imagePart = new FileBody(file, "image/jpeg");
				MultipartEntity pairs = new MultipartEntity();
				pairs.addPart("image", imagePart);
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
