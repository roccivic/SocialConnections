package com.placella.socialconnections;

import android.content.Context;

public class ListMenuItem {
	private int label;
	private String link;
	private int image;
	private Context ctx;
	public ListMenuItem(Context ctx, int label, String link, int image) {
		this.label = label;
		this.link = link;
		this.ctx = ctx;
		this.image = image;
	}
	public String getLink() {
		return link;
	}
	public String toString() {
		return ctx.getString(label);
	}
	public int getImage() {
		return image;
	}
}