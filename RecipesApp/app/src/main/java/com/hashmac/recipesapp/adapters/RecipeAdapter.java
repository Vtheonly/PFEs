package com.hashmac.recipesapp.adapters;

import android.content.Intent;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.hashmac.recipesapp.R;
import com.hashmac.recipesapp.RecipeDetailsActivity;
import com.hashmac.recipesapp.databinding.ItemRecipeBinding;
import com.hashmac.recipesapp.models.Recipe;

import java.util.ArrayList;
import java.util.List;

public class RecipeAdapter extends RecyclerView.Adapter<RecipeAdapter.RecipeHolder> {
    // Use final for the list and initialize it to prevent NullPointerExceptions
    private final List<Recipe> recipeList = new ArrayList<>();

    // Method to update the list and notify adapter
    public void setRecipeList(List<Recipe> newRecipeList) {
        this.recipeList.clear();
        if (newRecipeList != null) {
            this.recipeList.addAll(newRecipeList);
        }
        notifyDataSetChanged(); // Consider using DiffUtil for better performance
    }

    @NonNull
    @Override
    public RecipeHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // Inflate using the binding class directly
        ItemRecipeBinding binding = ItemRecipeBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false);
        return new RecipeHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull RecipeHolder holder, int position) {
        Recipe recipe = recipeList.get(position);
        if (recipe != null) { // Add null check for safety
            holder.bind(recipe);
        }
    }

    @Override
    public int getItemCount() {
        return recipeList.size();
    }

    public static class RecipeHolder extends RecyclerView.ViewHolder {
        // Make binding final as it's set in constructor and shouldn't change
        private final ItemRecipeBinding binding;

        public RecipeHolder(@NonNull ItemRecipeBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        // Renamed from onBind for clarity, matches convention
        public void bind(Recipe recipe) {
            // Load static placeholder using Glide
            Glide.with(binding.getRoot().getContext())
                    .load(R.drawable.image_placeholder) // Static placeholder drawable resource
                    .centerCrop()
                    .placeholder(R.drawable.image_placeholder) // Placeholder while loading (optional here)
                    .error(R.drawable.image_placeholder)      // Placeholder if loading fails
                    .into(binding.bgImgRecipe);

            binding.tvRecipeName.setText(recipe.getName() != null ? recipe.getName() : "Recipe Name"); // Handle potential null name

            binding.getRoot().setOnClickListener(view -> {
                Intent intent = new Intent(binding.getRoot().getContext(), RecipeDetailsActivity.class);
                intent.putExtra("recipe", recipe); // Recipe implements Serializable
                binding.getRoot().getContext().startActivity(intent);
            });
        }
    }
}