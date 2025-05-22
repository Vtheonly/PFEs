package com.example.libraryapp;

import androidx.appcompat.app.AppCompatActivity;

import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

public class MainActivity extends AppCompatActivity {

    private EditText editTextUsername, editTextPassword;
    private Button buttonLogin;
    private TextView textViewResult;
    private RequestQueue requestQueue;
    private static final String TAG = "MainActivity";

    // IMPORTANT: Replace with your actual server URL
    private static final String LOGIN_URL = "http://10.0.2.2/Library/login.php";
    // For a real device on the same network: "http://YOUR_COMPUTER_IP/Library/login.php"

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        editTextUsername = findViewById(R.id.editTextUsername);
        editTextPassword = findViewById(R.id.editTextPassword);
        buttonLogin = findViewById(R.id.buttonLogin);
        textViewResult = findViewById(R.id.textViewResult);
        
        requestQueue = Volley.newRequestQueue(this);

        buttonLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String username = editTextUsername.getText().toString().trim();
                String password = editTextPassword.getText().toString().trim();

                if (username.isEmpty() || password.isEmpty()) {
                    Toast.makeText(MainActivity.this, "Please enter username and password", Toast.LENGTH_SHORT).show();
                    return;
                }
                
                loginUser(username, password);
            }
        });
    }
    
    private void loginUser(final String username, final String password) {
        textViewResult.setText("Attempting login...");
        
        StringRequest stringRequest = new StringRequest(Request.Method.POST, LOGIN_URL,
            new Response.Listener<String>() {
                @Override
                public void onResponse(String response) {
                    Log.d(TAG, "Raw Server Response: " + response);
                    try {
                        JSONObject jsonObject = new JSONObject(response);
                        String status = jsonObject.getString("status");
                        String message = jsonObject.getString("message");

                        textViewResult.setText("Status: " + status + "\nMessage: " + message);

                        if ("success".equals(status)) {
                            Toast.makeText(MainActivity.this, "Login Successful!", Toast.LENGTH_LONG).show();
                            // You could navigate to another activity here
                            String userId = jsonObject.optString("user_id", "N/A");
                            String loggedInUsername = jsonObject.optString("username", "N/A");
                            textViewResult.append("\nUser ID: " + userId + "\nUsername: " + loggedInUsername);
                        } else {
                            Toast.makeText(MainActivity.this, "Login Failed: " + message, Toast.LENGTH_LONG).show();
                        }
                    } catch (JSONException e) {
                        textViewResult.setText("Error parsing JSON response: " + response);
                        Log.e(TAG, "JSON Parsing Error: " + e.getMessage() + "\nResponse: " + response);
                        Toast.makeText(MainActivity.this, "Error processing server response.", Toast.LENGTH_SHORT).show();
                    }
                }
            },
            new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {
                    String errorMessage = "Network Error: " + (error.getMessage() != null ? error.getMessage() : "Unknown error");
                    textViewResult.setText(errorMessage);
                    Log.e(TAG, errorMessage);
                    Toast.makeText(MainActivity.this, errorMessage, Toast.LENGTH_LONG).show();
                }
            }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("username", username);
                params.put("password", password);
                return params;
            }
        };

        // Add the request to the RequestQueue
        requestQueue.add(stringRequest);
    }
} 