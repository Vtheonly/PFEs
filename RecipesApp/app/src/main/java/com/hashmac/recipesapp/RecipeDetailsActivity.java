package com.hashmac.recipesapp;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat; // For getting colors safely

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Toast;

import com.bumptech.glide.Glide;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseUser;
import com.google.firebase.database.DataSnapshot;
import com.google.firebase.database.DatabaseError;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.database.ValueEventListener;
import com.hashmac.recipesapp.databinding.ActivityRecipeDetailsBinding;
import com.hashmac.recipesapp.models.FavouriteRecipe;
import com.hashmac.recipesapp.models.Recipe;
import com.hashmac.recipesapp.room.RecipeDatabase; // Import for executor
import com.hashmac.recipesapp.room.RecipeRepository;

public class RecipeDetailsActivity extends AppCompatActivity {
    ActivityRecipeDetailsBinding binding;
    private Recipe currentRecipe; // Store the current recipe object
    private RecipeRepository repository;
    private DatabaseReference recipeRef;
    private ValueEventListener recipeValueListener;
    private boolean isFavourite = false; // Track favourite state locally


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityRecipeDetailsBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        repository = new RecipeRepository(getApplication()); // Initialize repository

        currentRecipe = (Recipe) getIntent().getSerializableExtra("recipe");

        if (currentRecipe == null || currentRecipe.getId() == null) {
            Toast.makeText(this, "Error: Recipe data not found.", Toast.LENGTH_LONG).show();
            finish(); // Close activity if no recipe data
            return;
        }

        initView(currentRecipe);
        setupListeners(currentRecipe);
        checkFavoriteStatus(currentRecipe.getId()); // Initial check
        attachFirebaseListener(currentRecipe.getId()); // Listen for updates
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        // Detach listener when activity is destroyed
        detachFirebaseListener();
    }

    private void attachFirebaseListener(String recipeId) {
        // Detach previous listener if any
        detachFirebaseListener();

        recipeRef = FirebaseDatabase.getInstance().getReference("Recipes").child(recipeId);
        recipeValueListener = recipeRef.addValueEventListener(new ValueEventListener() {
            @Override
            public void onDataChange(@NonNull DataSnapshot snapshot) {
                if (!snapshot.exists()) {
                    // Recipe was deleted from Firebase
                    Toast.makeText(RecipeDetailsActivity.this, "Recipe no longer exists.", Toast.LENGTH_SHORT).show();
                    finish(); // Close the details screen
                    return;
                }
                Recipe updatedRecipe = snapshot.getValue(Recipe.class);
                if (updatedRecipe != null) {
                    currentRecipe = updatedRecipe; // Update the local recipe object
                    initView(currentRecipe); // Update the UI with new data
                    // Re-check favourite status in case ID changed (unlikely but possible)
                    // checkFavoriteStatus(currentRecipe.getId()); - Usually not needed unless ID changes
                } else {
                    Log.w("RecipeDetailsActivity", "Received null recipe data from listener for ID: " + recipeId);
                }
            }

            @Override
            public void onCancelled(@NonNull DatabaseError error) {
                Log.e("RecipeDetailsActivity", "Firebase listener cancelled: ", error.toException());
                Toast.makeText(RecipeDetailsActivity.this, "Error listening for recipe updates.", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void detachFirebaseListener() {
        if (recipeRef != null && recipeValueListener != null) {
            recipeRef.removeEventListener(recipeValueListener);
            recipeRef = null;
            recipeValueListener = null;
        }
    }

    // Populates the UI elements
    private void initView(Recipe recipe) {
        if (recipe == null) return; // Safety check

        binding.tvName.setText(recipe.getName() != null ? recipe.getName() : "Recipe Name");
        binding.tcCategory.setText(recipe.getCategory() != null ? recipe.getCategory() : "Category");
        binding.tvDescription.setText(recipe.getDescription() != null ? recipe.getDescription() : "Description not available.");
        binding.tvCalories.setText(String.format("%s Calories", recipe.getCalories() != null ? recipe.getCalories() : "N/A"));

        // Load static placeholder image using Glide
        Glide.with(this)
                .load(R.drawable.image_placeholder) // Use the placeholder resource ID
                .centerCrop()
                .placeholder(R.drawable.image_placeholder)
                .error(R.drawable.image_placeholder) // Show placeholder on error too
                .into(binding.imgRecipe);

        // Visibility of Edit/Delete buttons based on author
        FirebaseUser currentUser = FirebaseAuth.getInstance().getCurrentUser();
        boolean isAuthor = currentUser != null && recipe.getAuthorId() != null && recipe.getAuthorId().equals(currentUser.getUid());

        binding.imgEdit.setVisibility(isAuthor ? View.VISIBLE : View.GONE);
        binding.btnDelete.setVisibility(isAuthor ? View.VISIBLE : View.GONE);
    }

    // Sets up onClick listeners
    private void setupListeners(Recipe recipe) {
        if (recipe == null) return;

        binding.imgEdit.setOnClickListener(view -> {
            Intent intent = new Intent(this, AddRecipeActivity.class);
            intent.putExtra("recipe", recipe); // Pass the current recipe data
            intent.putExtra("isEdit", true);
            startActivity(intent);
        });

        binding.imgFvrt.setOnClickListener(view -> toggleFavourite(recipe));

        binding.btnDelete.setOnClickListener(view -> showDeleteConfirmationDialog(recipe));
    }

    // Checks favourite status from Room DB (on background thread)
    private void checkFavoriteStatus(String recipeId) {
        if (recipeId == null || repository == null) return;

        RecipeDatabase.databaseWriteExecutor.execute(() -> {
            boolean favourite = repository.isFavourite(recipeId);
            runOnUiThread(() -> { // Update UI on main thread
                isFavourite = favourite; // Update local state variable
                updateFavouriteButtonUI(isFavourite);
            });
        });
    }

    // Updates the favourite button's appearance
    private void updateFavouriteButtonUI(boolean isFav) {
        int colorRes = isFav ? R.color.accent : R.color.black;
        // Use ContextCompat for safe color retrieval
        binding.imgFvrt.setColorFilter(ContextCompat.getColor(this, colorRes));
    }


    // Handles adding/removing from favourites (Room DB on background thread)
    private void toggleFavourite(Recipe recipe) {
        if (recipe == null || recipe.getId() == null || repository == null) return;

        final String recipeId = recipe.getId(); // Use final variable for lambda

        RecipeDatabase.databaseWriteExecutor.execute(() -> {
            boolean currentlyFavourite = repository.isFavourite(recipeId); // Check DB state again before action
            if (currentlyFavourite) {
                repository.delete(new FavouriteRecipe(recipeId));
                isFavourite = false; // Update local state
            } else {
                repository.insert(new FavouriteRecipe(recipeId));
                isFavourite = true; // Update local state
            }
            // Update UI on the main thread after DB operation is done
            runOnUiThread(() -> updateFavouriteButtonUI(isFavourite));
        });
    }

    private void showDeleteConfirmationDialog(Recipe recipe) {
        if (recipe == null || recipe.getId() == null) return;

        new AlertDialog.Builder(this)
                .setTitle("Delete Recipe")
                .setMessage("Are you sure you want to delete this recipe? This action cannot be undone.")
                .setPositiveButton("Delete", (dialogInterface, i) -> deleteRecipeFromFirebase(recipe.getId()))
                .setNegativeButton("Cancel", (dialogInterface, i) -> dialogInterface.dismiss())
                .show();
    }

    private void deleteRecipeFromFirebase(String recipeId) {
        if (recipeId == null) return;

        ProgressDialog dialog = new ProgressDialog(this);
        dialog.setMessage("Deleting...");
        dialog.setCancelable(false);
        dialog.show();

        // Also remove from Room favourites if it exists there
        RecipeDatabase.databaseWriteExecutor.execute(() -> {
            if (repository.isFavourite(recipeId)) {
                repository.delete(new FavouriteRecipe(recipeId));
            }
        });

        // Remove from Firebase Realtime Database
        DatabaseReference reference = FirebaseDatabase.getInstance().getReference("Recipes");
        reference.child(recipeId).removeValue().addOnCompleteListener(task -> {
            dialog.dismiss();
            if (task.isSuccessful()) {
                Toast.makeText(this, "Recipe Deleted Successfully", Toast.LENGTH_SHORT).show();
                finish(); // Close the details activity after deletion
            } else {
                Log.e("RecipeDetailsActivity", "Failed to delete recipe: ", task.getException());
                Toast.makeText(this, "Failed to delete recipe. Please try again.", Toast.LENGTH_SHORT).show();
            }
        });
    }

    // Removed updateDataWithFireBase method as the listener handles updates now
}