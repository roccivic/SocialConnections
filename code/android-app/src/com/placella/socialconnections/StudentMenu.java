package com.placella.socialconnections;

import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;

public class StudentMenu extends Activity {

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_student_menu);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_student_menu, menu);
        return true;
    }
}
