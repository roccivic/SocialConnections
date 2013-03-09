package com.placella.socialconnections;

import java.util.List;

import android.content.Context;
import android.graphics.Color;
import android.view.*;
import android.view.ViewGroup.LayoutParams;
import android.widget.*;

public class MenuArrayAdapter extends ArrayAdapter<ListMenuItem> {
	private Context context;
	private List<ListMenuItem> objects;
	
	public MenuArrayAdapter(Context context, int resource,
			List<ListMenuItem> objects) {
		super(context, resource, objects);
		this.context = context;
		this.objects = objects;
	}
	
	public View getView(int position, View convertView, ViewGroup parent) {
		LinearLayout l = new LinearLayout(context);
        l.setOrientation(LinearLayout.HORIZONTAL);

        ImageView i = new ImageView(context);
        i.setImageResource(objects.get(position).getImage());
        l.addView(i);

        TextView t = new TextView(context);
        t.setTextSize(16);
        t.setTextColor(Color.BLACK);
		t.setText(objects.get(position).toString());
		t.setLayoutParams(
        	new LayoutParams(
        		LayoutParams.MATCH_PARENT,
        		LayoutParams.MATCH_PARENT
        	)
        );
		t.setGravity(Gravity.CENTER_VERTICAL);
		
        l.addView(t);

		return l;
	}
}
