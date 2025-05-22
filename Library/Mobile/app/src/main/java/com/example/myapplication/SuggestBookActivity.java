package com.example.myapplication;


import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.example.myapplication.network.VolleySingleton;
import com.example.myapplication.utils.Constants;
import com.example.myapplication.utils.SharedPrefManager;
import org.json.JSONException;
import org.json.JSONObject;

public class SuggestBookActivity extends AppCompatActivity {

    private EditText editTextTitle, editTextAuthor, editTextCategory;
    private Button buttonSubmitSuggestion;
    private ProgressBar progressBar;
    private int userId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_suggest_book);

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


        editTextTitle = findViewById(R.id.editTextBookTitleSuggest);
        editTextAuthor = findViewById(R.id.editTextAuthorSuggest);
        editTextCategory = findViewById(R.id.editTextCategorySuggest);
        buttonSubmitSuggestion = findViewById(R.id.buttonSubmitSuggestion);
        progressBar = findViewById(R.id.progressBarSuggest);

        buttonSubmitSuggestion.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                submitSuggestion();
            }
        });
    }

    private void submitSuggestion() {
        final String title = editTextTitle.getText().toString().trim();
        final String author = editTextAuthor.getText().toString().trim();
        final String category = editTextCategory.getText().toString().trim();

        if (TextUtils.isEmpty(title) || TextUtils.isEmpty(author) || TextUtils.isEmpty(category)) {
            Toast.makeText(this, "All fields are required", Toast.LENGTH_SHORT).show();
            return;
        }

        progressBar.setVisibility(View.VISIBLE);

        StringRequest stringRequest = new StringRequest(Request.Method.POST, Constants.SUGGEST_BOOK_URL,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        progressBar.setVisibility(View.GONE);
                        try {
                            JSONObject obj = new JSONObject(response);
                            String message = obj.optString("message", obj.optString("error")); 
                            Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG).show();
                            if (obj.has("message") && (message.contains("succès") || message.contains("success"))) {
                                
                                editTextTitle.setText("");
                                editTextAuthor.setText("");
                                editTextCategory.setText("");
                                finish(); 
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                            Toast.makeText(getApplicationContext(), "JSON Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        progressBar.setVisibility(View.GONE);
                        Toast.makeText(getApplicationContext(), "Network Error: " + error.getMessage(), Toast.LENGTH_LONG).show();
                    }
                }) {
            @Override
            public byte[] getBody() {
                JSONObject jsonBody = new JSONObject();
                try {
                    jsonBody.put("action", "create");
                    jsonBody.put("ID_Utilisateur", userId);
                    jsonBody.put("Titre", title);
                    jsonBody.put("Auteur", author);
                    jsonBody.put("Categorie", category);
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
        VolleySingleton.getInstance(this).addToRequestQueue(stringRequest);
    }
}