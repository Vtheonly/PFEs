
package com.example.myapplication.adapters; 

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64; 
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView; 
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.myapplication.R; 
import com.example.myapplication.models.Book;
import java.util.List;

public class BookAdapter extends RecyclerView.Adapter<BookAdapter.BookViewHolder> {

    private Context context;
    private List<Book> bookList;
    private OnBookReserveListener reserveListener;

    public interface OnBookReserveListener {
        void onReserveClicked(Book book);
    }

    public BookAdapter(Context context, List<Book> bookList, OnBookReserveListener reserveListener) {
        this.context = context;
        this.bookList = bookList;
        this.reserveListener = reserveListener;
    }

    @NonNull
    @Override
    public BookViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_book, parent, false);
        return new BookViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull BookViewHolder holder, int position) {
        Book book = bookList.get(position);
        holder.textViewTitle.setText(book.getTitre());
        holder.textViewAuthor.setText("By: " + book.getAuteur());
        holder.textViewCategory.setText("Category: " .concat(book.getCategorie() != null ? book.getCategorie() : "N/A"));
        holder.textViewStatus.setText("Status: " .concat(book.getStatut_Livre() != null ? book.getStatut_Livre() : "N/A"));


        
        String imageBase64 = book.getImageBase64();
        if (imageBase64 != null && !imageBase64.isEmpty()) {
            try {
                byte[] decodedString = Base64.decode(imageBase64, Base64.DEFAULT);
                Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                if (decodedByte != null) {
                    holder.imageViewCover.setImageBitmap(decodedByte);
                } else {
                    
                    holder.imageViewCover.setImageResource(R.drawable.ic_launcher_background); 
                }
            } catch (IllegalArgumentException e) {
                
                e.printStackTrace();
                holder.imageViewCover.setImageResource(R.drawable.ic_launcher_background); 
            }
        } else {
            
            holder.imageViewCover.setImageResource(R.drawable.ic_launcher_background); 
        }


        if ("Disponible".equalsIgnoreCase(book.getStatut_Livre())) {
            holder.buttonReserve.setVisibility(View.VISIBLE);
            holder.buttonReserve.setOnClickListener(v -> {
                if (reserveListener != null) {
                    reserveListener.onReserveClicked(book);
                }
            });
        } else {
            holder.buttonReserve.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemCount() {
        return bookList.size();
    }

    public void updateBooks(List<Book> newBooks) {
        this.bookList.clear();
        this.bookList.addAll(newBooks);
        notifyDataSetChanged();
    }

    public void clearBooks() {
        this.bookList.clear();
        notifyDataSetChanged();
    }


    static class BookViewHolder extends RecyclerView.ViewHolder {
        TextView textViewTitle, textViewAuthor, textViewCategory, textViewStatus;
        ImageView imageViewCover; 
        Button buttonReserve;

        public BookViewHolder(@NonNull View itemView) {
            super(itemView);
            imageViewCover = itemView.findViewById(R.id.imageViewBookCoverItem); 
            textViewTitle = itemView.findViewById(R.id.textViewBookTitleItem);
            textViewAuthor = itemView.findViewById(R.id.textViewBookAuthorItem);
            textViewCategory = itemView.findViewById(R.id.textViewBookCategoryItem);
            textViewStatus = itemView.findViewById(R.id.textViewBookStatusItem);
            buttonReserve = itemView.findViewById(R.id.buttonReserveThisBook);
        }
    }
}