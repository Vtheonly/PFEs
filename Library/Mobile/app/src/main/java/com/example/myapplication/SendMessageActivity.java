package com.example.myapplication;


import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.example.myapplication.network.VolleySingleton;
import com.example.myapplication.utils.Constants;
import com.example.myapplication.utils.SharedPrefManager;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;

public class SendMessageActivity extends AppCompatActivity {

    private EditText editTextMessageContent;
    private Button buttonSubmitMessage;
    private ProgressBar progressBar;
    private int userId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_send_message);

        if (!SharedPrefManager.getInstance(this).isLoggedIn()) {
            finish();
            return;
        }
        userId = SharedPrefManager.getInstance(this).getUserId();
        if (userId == -1) {
            Toast.makeText(this, "User not identified. Please login again.", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        editTextMessageContent = findViewById(R.id.editTextMessageContent);
        buttonSubmitMessage = findViewById(R.id.buttonSubmitMessage);
        progressBar = findViewById(R.id.progressBarMessage);

        buttonSubmitMessage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                submitMessage();
            }
        });
    }


    private void submitMessage() {
        final String content = editTextMessageContent.getText().toString().trim();

        if (TextUtils.isEmpty(content)) {
            editTextMessageContent.setError("Message cannot be empty");
            editTextMessageContent.requestFocus();
            return;
        }

        progressBar.setVisibility(View.VISIBLE);

        StringRequest stringRequest = new StringRequest(Request.Method.POST, Constants.SEND_MESSAGE_URL,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        progressBar.setVisibility(View.GONE);
                        Log.d("SendMessageResponse", "Raw response: " + response);

                        try {
                            JSONObject obj = new JSONObject(response);
                            if (obj.has("message")) {
                                String successMessage = obj.getString("message");
                                Toast.makeText(getApplicationContext(), "Server: " + successMessage, Toast.LENGTH_LONG).show();
                                finish();
                            } else if (obj.has("error")) {
                                String errorMessage = obj.getString("error");
                                Toast.makeText(getApplicationContext(), "Server Error: " + errorMessage, Toast.LENGTH_LONG).show();
                            } else {
                                Toast.makeText(getApplicationContext(), "Unknown server response structure.", Toast.LENGTH_LONG).show();
                            }
                        } catch (JSONException e) {
                            Toast.makeText(getApplicationContext(), "Error parsing server response.", Toast.LENGTH_LONG).show();
                            Log.e("SendMessageError", "JSON parse failed: " + e.getMessage());
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        progressBar.setVisibility(View.GONE);

                        String errorMessage = "Network Error";
                        if (error != null && error.networkResponse != null) {
                            try {
                                String responseBody = new String(error.networkResponse.data, "utf-8");
                                errorMessage += "\n" + responseBody;
                            } catch (UnsupportedEncodingException e) {
                                Log.e("SendMessageError", "Encoding error: " + e.getMessage());
                            }
                        }

                        Toast.makeText(getApplicationContext(), errorMessage, Toast.LENGTH_LONG).show();
                        Log.e("SendMessageError", "Volley error: " + error);
                    }
                }) {

            
            @Override
            public byte[] getBody() {
                JSONObject jsonBody = new JSONObject();
                try {
                    jsonBody.put("action", "send");
                    jsonBody.put("Expediteur_ID", userId);
                    jsonBody.put("Contenu", content);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                return jsonBody.toString().getBytes();
            }

            @Override
            public String getBodyContentType() {
                return "application/json; charset=utf-8";
            }
        };

        stringRequest.setRetryPolicy(new DefaultRetryPolicy(
                20000,
                DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT));

        VolleySingleton.getInstance(this).addToRequestQueue(stringRequest);
    }



}