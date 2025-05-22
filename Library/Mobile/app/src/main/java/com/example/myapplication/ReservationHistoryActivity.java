package com.example.myapplication;

import android.os.Bundle;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.example.myapplication.adapters.ReservationHistoryAdapter;
import com.example.myapplication.models.ReservationHistoryItem;
import com.example.myapplication.network.VolleySingleton;
import com.example.myapplication.utils.Constants;
import com.example.myapplication.utils.SharedPrefManager;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.ArrayList;
import java.util.List;

public class ReservationHistoryActivity extends AppCompatActivity {

    private static final String TAG = "ReservationHistory";
    private RecyclerView recyclerViewHistory;
    private ReservationHistoryAdapter adapter;
    private List<ReservationHistoryItem> historyItemList;
    private ProgressBar progressBarHistory;
    private TextView textViewNoHistory;
    private int userId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reservation_history);

        Toolbar toolbar = findViewById(R.id.toolbarHistory);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle("Historique des Réservations");
        }

        recyclerViewHistory = findViewById(R.id.recyclerViewHistory);
        progressBarHistory = findViewById(R.id.progressBarHistory);
        textViewNoHistory = findViewById(R.id.textViewNoHistory);

        recyclerViewHistory.setLayoutManager(new LinearLayoutManager(this));
        historyItemList = new ArrayList<>();
        adapter = new ReservationHistoryAdapter(this, historyItemList);
        recyclerViewHistory.setAdapter(adapter);

        if (!SharedPrefManager.getInstance(this).isLoggedIn()) {
            Toast.makeText(this, "Veuillez vous connecter.", Toast.LENGTH_LONG).show();
            finish(); 
            return;
        }
        userId = SharedPrefManager.getInstance(this).getUserId();

        fetchReservationHistory();
    }

    private void fetchReservationHistory() {
        progressBarHistory.setVisibility(View.VISIBLE);
        textViewNoHistory.setVisibility(View.GONE);
        recyclerViewHistory.setVisibility(View.GONE);

        String url = Constants.RESERVATION_HISTORY_URL + userId;
        Log.d(TAG, "Fetching history from: " + url);

        StringRequest stringRequest = new StringRequest(Request.Method.GET, url,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        Log.d(TAG, "Response: " + response);
                        progressBarHistory.setVisibility(View.GONE);
                        try {
                            JSONObject jsonObject = new JSONObject(response);
                            String message = jsonObject.getString("message");

                            if (jsonObject.has("data")) {
                                JSONArray dataArray = jsonObject.getJSONArray("data");
                                if (dataArray.length() == 0) {
                                    textViewNoHistory.setVisibility(View.VISIBLE);
                                    recyclerViewHistory.setVisibility(View.GONE);
                                } else {
                                    historyItemList.clear();
                                    for (int i = 0; i < dataArray.length(); i++) {
                                        JSONObject itemObj = dataArray.getJSONObject(i);
                                        ReservationHistoryItem item = new ReservationHistoryItem(
                                                itemObj.getInt("ID_Reservation"),
                                                itemObj.getInt("ID_Livre"),                 
                                                itemObj.getString("Titre_Livre"),
                                                itemObj.getString("Auteur_Livre"),
                                                itemObj.getString("Categorie_Livre"),
                                                itemObj.optString("Image_Livre_Base64", null),
                                                itemObj.getString("Date_Reservation"),
                                                itemObj.getString("Date_Retour_Prevue"),   
                                                null,                                      
                                                itemObj.getString("Statut")                
                                        );
                                        historyItemList.add(item);
                                    }
                                    adapter.notifyDataSetChanged();
                                    recyclerViewHistory.setVisibility(View.VISIBLE);
                                    textViewNoHistory.setVisibility(View.GONE);
                                }
                            } else { 
                                Toast.makeText(ReservationHistoryActivity.this, message, Toast.LENGTH_LONG).show();
                                textViewNoHistory.setVisibility(View.VISIBLE);
                            }

                        } catch (JSONException e) {
                            Log.e(TAG, "JSON Parsing error: " + e.getMessage());
                            Toast.makeText(ReservationHistoryActivity.this, "Erreur de format des données.", Toast.LENGTH_LONG).show();
                            textViewNoHistory.setVisibility(View.VISIBLE);
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        progressBarHistory.setVisibility(View.GONE);
                        textViewNoHistory.setVisibility(View.VISIBLE);
                        Log.e(TAG, "Volley error: " + error.toString());
                        Toast.makeText(ReservationHistoryActivity.this, "Erreur réseau: " + error.getMessage(), Toast.LENGTH_LONG).show();
                    }
                });

        VolleySingleton.getInstance(this).addToRequestQueue(stringRequest);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            onBackPressed();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
}