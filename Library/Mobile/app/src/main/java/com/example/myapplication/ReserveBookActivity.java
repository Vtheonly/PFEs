package com.example.myapplication;



import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.example.myapplication.adapters.BookAdapter;
import com.example.myapplication.models.Book;
import com.example.myapplication.network.VolleySingleton;
import com.example.myapplication.utils.Constants;
import com.example.myapplication.utils.SharedPrefManager;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;

public class ReserveBookActivity extends AppCompatActivity implements BookAdapter.OnBookReserveListener {

    private EditText editTextSearchQuery;
    private RadioGroup radioGroupSearchCriteria;
    private Button buttonSearchBooks;
    private RecyclerView recyclerViewBooks;
    private BookAdapter bookAdapter;
    private List<Book> bookList;
    private ProgressBar progressBarSearch, progressBarReserve;
    private TextView textViewNoBooksFound;
    private int userId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reserve_book);

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

        editTextSearchQuery = findViewById(R.id.editTextSearchQuery);
        radioGroupSearchCriteria = findViewById(R.id.radioGroupSearchCriteria);
        buttonSearchBooks = findViewById(R.id.buttonSearchBooks);
        recyclerViewBooks = findViewById(R.id.recyclerViewBooks);
        progressBarSearch = findViewById(R.id.progressBarSearchBooks);
        progressBarReserve = findViewById(R.id.progressBarReserve); 
        textViewNoBooksFound = findViewById(R.id.textViewNoBooksFound);

        bookList = new ArrayList<>();
        bookAdapter = new BookAdapter(this, bookList, this); 
        recyclerViewBooks.setLayoutManager(new LinearLayoutManager(this));
        recyclerViewBooks.setAdapter(bookAdapter);

        buttonSearchBooks.setOnClickListener(v -> searchBooks());
    }

    private void searchBooks() {
        final String query = editTextSearchQuery.getText().toString().trim();
        if (TextUtils.isEmpty(query)) {
            Toast.makeText(this, "Please enter a search term", Toast.LENGTH_SHORT).show();
            return;
        }

        int selectedRadioId = radioGroupSearchCriteria.getCheckedRadioButtonId();
        RadioButton selectedRadioButton = findViewById(selectedRadioId);
        String searchType = "";
        String searchParamKey = "";

        if (selectedRadioButton.getId() == R.id.radioButtonTitle) {
            searchType = "Titre";
            searchParamKey = "Titre";
        } else if (selectedRadioButton.getId() == R.id.radioButtonAuthor) {
            searchType = "Auteur";
            searchParamKey = "Auteur";
        } else if (selectedRadioButton.getId() == R.id.radioButtonCategory) {
            searchType = "Categorie";
            searchParamKey = "Categorie";
        }

        if (searchParamKey.isEmpty()) {
            Toast.makeText(this, "Please select a search criterion", Toast.LENGTH_SHORT).show();
            return;
        }

        progressBarSearch.setVisibility(View.VISIBLE);
        textViewNoBooksFound.setVisibility(View.GONE);
        bookAdapter.clearBooks(); 

        String encodedQuery;
        try {
            encodedQuery = URLEncoder.encode(query, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
            progressBarSearch.setVisibility(View.GONE);
            Toast.makeText(this, "Error encoding search query", Toast.LENGTH_SHORT).show();
            return;
        }

        String url = Constants.API_LIVRE_URL + "?" + searchParamKey + "=" + encodedQuery;
        Log.d("SearchURL", "URL: " + url);


        StringRequest stringRequest = new StringRequest(Request.Method.GET, url,
                response -> {
                    progressBarSearch.setVisibility(View.GONE);
                    Log.d("SearchResponse", "Response: " + response);
                    try {
                        JSONObject obj = new JSONObject(response);
                        
                        if (obj.has("livres")) {
                            JSONArray booksArray = obj.getJSONArray("livres");
                            if (booksArray.length() == 0) {
                                textViewNoBooksFound.setVisibility(View.VISIBLE);
                            } else {
                                textViewNoBooksFound.setVisibility(View.GONE);
                            }
                            bookList.clear(); 
                            for (int i = 0; i < booksArray.length(); i++) {
                                JSONObject bookJson = booksArray.getJSONObject(i);
                                Book book = new Book(
                                        bookJson.getInt("ID_Livre"),
                                        bookJson.getString("Titre"),
                                        bookJson.getString("Auteur"),
                                        bookJson.getString("Categorie"),
                                        bookJson.optString("Statut_Livre", "N/A"),
                                        bookJson.optString("Image", null) 
                                );
                                bookList.add(book);
                            }
                            bookAdapter.notifyDataSetChanged(); 
                        } else if (obj.has("message")) { 
                            String message = obj.getString("message");
                            if ("Aucun résultat trouvé".equalsIgnoreCase(message) || bookList.isEmpty()) {
                                textViewNoBooksFound.setVisibility(View.VISIBLE);
                            } else {
                                Toast.makeText(ReserveBookActivity.this, message, Toast.LENGTH_SHORT).show();
                            }
                        } else {
                            textViewNoBooksFound.setVisibility(View.VISIBLE);
                        }

                    } catch (JSONException e) {
                        e.printStackTrace();
                        Log.e("SearchError", "JSON Parsing Error: " + e.getMessage());
                        Toast.makeText(getApplicationContext(), "Error parsing book data: " + e.getMessage(), Toast.LENGTH_LONG).show();
                        textViewNoBooksFound.setVisibility(View.VISIBLE);
                    }
                },
                error -> {
                    progressBarSearch.setVisibility(View.GONE);
                    textViewNoBooksFound.setVisibility(View.VISIBLE);
                    Log.e("SearchError", "Volley Error: " + error.toString());
                    if (error.networkResponse != null) {
                        Log.e("SearchError", "Status Code: " + error.networkResponse.statusCode);
                    }
                    Toast.makeText(getApplicationContext(), "Network Error searching books: " + error.getMessage(), Toast.LENGTH_LONG).show();
                });

        VolleySingleton.getInstance(this).addToRequestQueue(stringRequest);
    }

    @Override
    public void onReserveClicked(Book book) {
        
        if (book == null) return;

        progressBarReserve.setVisibility(View.VISIBLE); 

        StringRequest stringRequest = new StringRequest(Request.Method.POST, Constants.RESERVE_BOOK_URL,
                response -> {
                    progressBarReserve.setVisibility(View.GONE);
                    try {
                        JSONObject obj = new JSONObject(response);
                        String message = obj.optString("message", obj.optString("error"));
                        Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG).show();
                        if (obj.has("message") && (message.toLowerCase().contains("succès") || message.toLowerCase().contains("success"))) {
                            
                            searchBooks(); 
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                        Toast.makeText(getApplicationContext(), "JSON Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    }
                },
                error -> {
                    progressBarReserve.setVisibility(View.GONE);
                    Toast.makeText(getApplicationContext(), "Network Error reserving book: " + error.getMessage(), Toast.LENGTH_LONG).show();
                }) {
            @Override
            public byte[] getBody() {
                JSONObject jsonBody = new JSONObject();
                try {
                    jsonBody.put("action", "create");
                    jsonBody.put("ID_Utilisateur", userId);
                    jsonBody.put("ID_Livre", book.getID_Livre());
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