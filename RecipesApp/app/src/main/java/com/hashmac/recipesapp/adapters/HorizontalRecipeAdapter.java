package com.hashmac.recipesapp.adapters;


import android.content.Intent;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.hashmac.recipesapp.R;
import com.hashmac.recipesapp.RecipeDetailsActivity;
import com.hashmac.recipesapp.databinding.ItemRecipeHorizontalBinding; // Correct binding import
import com.hashmac.recipesapp.models.Recipe;

import java.util.ArrayList;
import java.util.List;

public class HorizontalRecipeAdapter extends RecyclerView.Adapter<HorizontalRecipeAdapter.RecipeHolder> {
    // Use final and initialize
    public final List<Recipe> recipeList = new ArrayList<>();

    public void setRecipeList(List<Recipe> newRecipeList) {
        this.recipeList.clear();
        if (newRecipeList != null) {
            this.recipeList.addAll(newRecipeList);
        }
        notifyDataSetChanged(); // Consider DiffUtil
    }

    @NonNull
    @Override
    public RecipeHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // Use correct binding class
        ItemRecipeHorizontalBinding binding = ItemRecipeHorizontalBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false);
        return new RecipeHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull RecipeHolder holder, int position) {
        Recipe recipe = recipeList.get(position);
        if (recipe != null) { // Null check
            holder.bind(recipe);
        }
    }

    @Override
    public int getItemCount() {
        return recipeList.size();
    }

    public static class RecipeHolder extends RecyclerView.ViewHolder {
        // Use correct binding class and make final
        private final ItemRecipeHorizontalBinding binding;

        public RecipeHolder(@NonNull ItemRecipeHorizontalBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        // Renamed for clarity
        public void bind(Recipe recipe) {
            // Load static placeholder using Glide
            Glide.with(binding.getRoot().getContext())
                    .load(R.drawable.image_placeholder) // Static placeholder
                    .centerCrop()
                    .placeholder(R.drawable.image_placeholder)
                    .error(R.drawable.image_placeholder)
                    .into(binding.bgImgRecipe);

            binding.tvRecipeName.setText(recipe.getName() != null ? recipe.getName() : "Recipe Name"); // Handle null name

            binding.getRoot().setOnClickListener(view -> {
                Intent intent = new Intent(binding.getRoot().getContext(), RecipeDetailsActivity.class);
                intent.putExtra("recipe", recipe);
                binding.getRoot().getContext().startActivity(intent);
            });
        }
    }
}