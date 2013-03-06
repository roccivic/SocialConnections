package com.placella.socialconnections;

import android.content.Context;

public class ListMenuItem {
	private int label;
	private String link;
	private Context ctx;
	public ListMenuItem(Context ctx, int label, String link) {
		this.label = label;
		this.link = link;
		this.ctx = ctx;
	}
	public String getLink() {
		return link;
	}
	public String toString() {
		return ctx.getString(label);
	}
}