package com.placella.socialconnections;

import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;
import android.widget.TextView;

public class Attendance extends Activity {

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_attendance);
        
        //Run a test to see if path is correct
        String path = "";
        Bundle extras = getIntent().getExtras();
        if (extras != null) {
            path = extras.getString("path");
        }
        TextView attendanceTV = (TextView) findViewById(R.id.attendanceTV);
        attendanceTV.setText(path);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_attendance, menu);
        return true;
    }
}
