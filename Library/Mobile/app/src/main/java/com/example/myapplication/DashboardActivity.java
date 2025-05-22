package com.example.myapplication;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import com.example.myapplication.utils.SharedPrefManager;

public class DashboardActivity extends AppCompatActivity {

    private TextView textViewWelcome;
    private Button buttonSuggestBook, buttonReserveBook, buttonSendMessage, buttonLogout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        if (!SharedPrefManager.getInstance(this).isLoggedIn()) {
            finish();
            startActivity(new Intent(this, com.example.myapplication.LoginActivity.class));
            return;
        }

        textViewWelcome = findViewById(R.id.textViewWelcome);
        buttonSuggestBook = findViewById(R.id.buttonSuggestBook);
        buttonReserveBook = findViewById(R.id.buttonReserveBook);
        buttonSendMessage = findViewById(R.id.buttonSendMessage);
        buttonLogout = findViewById(R.id.buttonLogout);
        Button btnHistory = findViewById(R.id.buttonViewReservationHistory);

        String userName = SharedPrefManager.getInstance(this).getUserName();
        if (userName != null) {
            textViewWelcome.setText(String.format(getString(R.string.dashboard_welcome), userName));
        } else {
            textViewWelcome.setText(String.format(getString(R.string.dashboard_welcome), "User"));
        }


        buttonSuggestBook.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(DashboardActivity.this, SuggestBookActivity.class));
            }
        });

        buttonReserveBook.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(DashboardActivity.this, ReserveBookActivity.class));
            }
        });

        buttonSendMessage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(DashboardActivity.this, SendMessageActivity.class));
            }
        });

        buttonLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                SharedPrefManager.getInstance(getApplicationContext()).logout();
                finish();
                startActivity(new Intent(DashboardActivity.this, LoginActivity.class));
            }
        });



        btnHistory.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(DashboardActivity.this, ReservationHistoryActivity.class));
            }
        });

    }
}