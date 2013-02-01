package com.placella.socialconnections;

import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;

public class LecturerMenu extends Activity {

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lecturer_menu);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_lecturer_menu, menu);
        return true;
    }
}
