package com.example.myapplication.adapters;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R;
import com.example.myapplication.models.ReservationHistoryItem;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;

public class ReservationHistoryAdapter extends RecyclerView.Adapter<ReservationHistoryAdapter.HistoryViewHolder> {

    private Context context;
    private List<ReservationHistoryItem> historyList;
    private SimpleDateFormat dbDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
    
    private SimpleDateFormat inputDateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()); 
    private SimpleDateFormat inputDateTimeFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()); 
    private SimpleDateFormat displayDateFormat = new SimpleDateFormat("dd MMM yyyy", Locale.getDefault());


    public ReservationHistoryAdapter(Context context, List<ReservationHistoryItem> historyList) {
        this.context = context;
        this.historyList = historyList;
    }

    @NonNull
    @Override
    public HistoryViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.list_item_reservation_history, parent, false);
        return new HistoryViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull HistoryViewHolder holder, int position) {
        ReservationHistoryItem item = historyList.get(position);

        holder.tvTitle.setText(item.getTitreLivre());
        holder.tvAuthor.setText(item.getAuteurLivre());
        holder.tvStatus.setText("Statut: " + item.getStatutReservation());

        holder.tvReservationDate.setText("Réservé le: " + formatDate(item.getDateReservation()));
        holder.tvDueDate.setText("Retour prévu le: " + formatDate(item.getDateRetourPrevu()));

        if (item.getDateRetourEffectif() != null && !item.getDateRetourEffectif().isEmpty() && !item.getDateRetourEffectif().equals("0000-00-00 00:00:00")) {
            holder.tvReturnDate.setText("Retourné le: " + formatDate(item.getDateRetourEffectif()));
            holder.tvReturnDate.setVisibility(View.VISIBLE);
        } else {
            holder.tvReturnDate.setVisibility(View.GONE);
        }

        
        String base64Image = item.getImageLivreBase64();
        if (base64Image != null && !base64Image.isEmpty()) {
            try {
                byte[] decodedString = Base64.decode(base64Image, Base64.DEFAULT);
                Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                if (decodedByte != null) {
                    holder.ivCover.setImageBitmap(decodedByte);
                } else {
                    holder.ivCover.setImageResource(R.drawable.ic_default_book_cover); 
                }
            } catch (IllegalArgumentException e) {
                holder.ivCover.setImageResource(R.drawable.ic_default_book_cover); 
            }
        } else {
            holder.ivCover.setImageResource(R.drawable.ic_default_book_cover); 
        }
    }



    private String formatDate(String dateString) {
        if (dateString == null || dateString.isEmpty() ||
                dateString.equals("0000-00-00") || dateString.equals("0000-00-00 00:00:00")) {
            return "N/A";
        }
        try {
            
            Date date = inputDateFormat.parse(dateString);
            return displayDateFormat.format(date);
        } catch (ParseException e) {
            
            try {
                Date date = inputDateTimeFormat.parse(dateString);
                return displayDateFormat.format(date);
            } catch (ParseException ex) {
                Log.w("FormatDate", "Could not parse date string: " + dateString);
                return dateString; 
            }
        }
    }


    @Override
    public int getItemCount() {
        return historyList == null ? 0 : historyList.size();
    }

    static class HistoryViewHolder extends RecyclerView.ViewHolder {
        ImageView ivCover;
        TextView tvTitle, tvAuthor, tvReservationDate, tvDueDate, tvReturnDate, tvStatus;

        public HistoryViewHolder(@NonNull View itemView) {
            super(itemView);
            ivCover = itemView.findViewById(R.id.imageViewBookCoverHistory);
            tvTitle = itemView.findViewById(R.id.textViewBookTitleHistory);
            tvAuthor = itemView.findViewById(R.id.textViewBookAuthorHistory);
            tvReservationDate = itemView.findViewById(R.id.textViewReservationDateHistory);
            tvDueDate = itemView.findViewById(R.id.textViewDueDateHistory);
            tvReturnDate = itemView.findViewById(R.id.textViewReturnDateHistory);
            tvStatus = itemView.findViewById(R.id.textViewReservationStatusHistory);
        }
    }
}