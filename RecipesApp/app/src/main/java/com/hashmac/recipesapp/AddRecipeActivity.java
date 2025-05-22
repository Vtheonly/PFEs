package com.hashmac.recipesapp;

import static java.lang.System.currentTimeMillis;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import android.app.ProgressDialog;
import android.graphics.Bitmap;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.Toast;

import com.bumptech.glide.Glide;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.database.DataSnapshot;
import com.google.firebase.database.DatabaseError;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.database.ValueEventListener;
// Firebase Storage imports removed as they are no longer used
// import com.google.firebase.storage.FirebaseStorage;
// import com.google.firebase.storage.StorageReference;
// import com.google.firebase.storage.UploadTask;
import com.hashmac.recipesapp.databinding.ActivityAddRecipeBinding;
import com.hashmac.recipesapp.models.Category;
import com.hashmac.recipesapp.models.Recipe;
// PickImage imports removed as they are no longer used
// import com.vansuita.pickimage.bundle.PickSetup;
// import com.vansuita.pickimage.dialog.PickImageDialog;

import java.io.ByteArrayOutputStream;
import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

public class AddRecipeActivity extends AppCompatActivity {
    ActivityAddRecipeBinding binding;
    // private boolean isImageSelected = false; // No longer needed as we use placeholder
    private ProgressDialog dialog;
    boolean isEdit;
    String recipeId;
    Recipe existingRecipeData; // To hold recipe data during edit

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityAddRecipeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        loadCategories();

        binding.btnAddRecipe.setOnClickListener(view -> {
            getData();
        });

        // Disable image selection
        binding.imgRecipe.setEnabled(false);
        binding.imgRecipe.setImageResource(R.drawable.image_placeholder); // Set static image
        // binding.imgRecipe.setOnClickListener(view -> {
        //     pickImage();
        // });

        isEdit = getIntent().getBooleanExtra("isEdit", false);
        if (isEdit) {
            existingRecipeData = (Recipe) getIntent().getSerializableExtra("recipe");
            if (existingRecipeData != null) {
                recipeId = existingRecipeData.getId();
                editRecipe(existingRecipeData);
            } else {
                Toast.makeText(this, "Error loading recipe data for editing.", Toast.LENGTH_SHORT).show();
                finish();
            }
        }
    }

    private void editRecipe(Recipe recipe) {
        // isImageSelected = true; // No longer tracks selection
        binding.etRecipeName.setText(recipe.getName());
        binding.etDescription.setText(recipe.getDescription());
        binding.etCookingTime.setText(recipe.getTime());
        binding.etCategory.setText(recipe.getCategory());
        binding.etCalories.setText(recipe.getCalories());

        // Load static placeholder for editing view
        Glide.with(binding.getRoot().getContext())
                .load(R.drawable.image_placeholder) // Load static placeholder
                .centerCrop()
                //.placeholder(R.drawable.image_placeholder) // Redundant
                .error(R.drawable.image_placeholder) // Show placeholder on error too
                .into(binding.imgRecipe);

        binding.btnAddRecipe.setText(R.string.update_recipe); // Use string resource
    }

    private void loadCategories() {
        List<String> categories = new ArrayList<>();
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, categories);
        binding.etCategory.setAdapter(adapter);
        DatabaseReference reference = FirebaseDatabase.getInstance().getReference().child("Categories");
        reference.addListenerForSingleValueEvent(new ValueEventListener() {
            @Override
            public void onDataChange(@NonNull DataSnapshot snapshot) {
                categories.clear(); // Clear list before adding new data
                if (snapshot.exists() && snapshot.hasChildren()) {
                    for (DataSnapshot dataSnapshot : snapshot.getChildren()) {
                        Category category = dataSnapshot.getValue(Category.class);
                        if (category != null && category.getName() != null) {
                            categories.add(category.getName());
                        }
                    }
                    adapter.notifyDataSetChanged();
                }
            }

            @Override
            public void onCancelled(@NonNull DatabaseError error) {
                Log.e("AddRecipeActivity", "Error loading categories: " + error.getMessage());
                Toast.makeText(AddRecipeActivity.this, "Failed to load categories.", Toast.LENGTH_SHORT).show();
            }
        });
    }

    // private void pickImage() {
    //     PickImageDialog.build(new PickSetup()).show(AddRecipeActivity.this).setOnPickResult(r -> {
    //         Log.e("AddRecipeActivity", "onPickResult: " + r.getUri());
    //         binding.imgRecipe.setImageBitmap(r.getBitmap());
    //         binding.imgRecipe.setScaleType(ImageView.ScaleType.CENTER_CROP);
    //         isImageSelected = true;
    //     }).setOnPickCancel(() -> Toast.makeText(AddRecipeActivity.this, "Cancelled", Toast.LENGTH_SHORT).show());
    // }

    private void getData() {
        String recipeName = binding.etRecipeName.getText() != null ? binding.etRecipeName.getText().toString().trim() : "";
        String recipeDescription = binding.etDescription.getText() != null ? binding.etDescription.getText().toString().trim() : "";
        String cookingTime = binding.etCookingTime.getText() != null ? binding.etCookingTime.getText().toString().trim() : "";
        String recipeCategory = binding.etCategory.getText() != null ? binding.etCategory.getText().toString().trim() : "";
        String calories = binding.etCalories.getText() != null ? binding.etCalories.getText().toString().trim() : "";

        // Validation
        if (recipeName.isEmpty()) {
            binding.etRecipeName.setError("Please enter Recipe Name");
            binding.etRecipeName.requestFocus(); // Focus the field
        } else if (recipeDescription.isEmpty()) {
            binding.etDescription.setError("Please enter Recipe Description");
            binding.etDescription.requestFocus();
        } else if (cookingTime.isEmpty()) {
            binding.etCookingTime.setError("Please enter Cooking Time");
            binding.etCookingTime.requestFocus();
        } else if (recipeCategory.isEmpty()) {
            binding.etCategory.setError("Please select Recipe Category");
            binding.etCategory.requestFocus();
            // Maybe show dropdown if using AutoCompleteTextView
            // binding.etCategory.showDropDown();
        } else if (calories.isEmpty()) {
            binding.etCalories.setError("Please enter Calories");
            binding.etCalories.requestFocus();
            // } else if (!isImageSelected && !isEdit) { // Removed image selection check
            //     Toast.makeText(this, "Please select an image", Toast.LENGTH_SHORT).show();
        } else {
            if (FirebaseAuth.getInstance().getCurrentUser() == null) {
                Toast.makeText(this, "Error: Not logged in.", Toast.LENGTH_SHORT).show();
                return; // Cannot save without author ID
            }
            String authorId = FirebaseAuth.getInstance().getUid();

            dialog = new ProgressDialog(this);
            dialog.setMessage(isEdit ? "Updating Recipe..." : "Adding Recipe...");
            dialog.setCancelable(false);
            dialog.show();

            // Use empty string for image URL as we are not uploading
            String imageUrlToSave = ""; // Default for new recipes

            // If editing, retain the existing image URL (even if empty) unless a new one *was* selected (which is now removed)
            if (isEdit && existingRecipeData != null && existingRecipeData.getImage() != null) {
                imageUrlToSave = existingRecipeData.getImage();
            }

            Recipe recipe = new Recipe(recipeName, recipeDescription, cookingTime, recipeCategory, calories, imageUrlToSave, authorId);

            if (isEdit) {
                recipe.setId(recipeId); // Set the existing ID for update
            }

            // Directly call saveDataInDataBase, passing the recipe WITHOUT uploading image first
            saveDataInDataBase(recipe);
        }
    }

    // Removed uploadImage method

    // Modified saveDataInDataBase signature and logic
    private void saveDataInDataBase(Recipe recipe) {
        // Ensure the image path isn't null before saving (Firebase might not like null)
        if (recipe.getImage() == null) {
            recipe.setImage("");
        }
        // Ensure authorId is not null or empty
        if (recipe.getAuthorId() == null || recipe.getAuthorId().isEmpty()) {
            Log.e("AddRecipeActivity", "Author ID is missing.");
            Toast.makeText(this, "Error: Author ID missing.", Toast.LENGTH_SHORT).show();
            if (dialog != null) dialog.dismiss();
            return;
        }

        DatabaseReference reference = FirebaseDatabase.getInstance().getReference().child("Recipes");

        if (isEdit) {
            // Ensure recipe ID is set for editing
            if (recipe.getId() == null || recipe.getId().isEmpty()) {
                Log.e("AddRecipeActivity", "Recipe ID is missing during edit save.");
                Toast.makeText(this, "Error: Recipe ID missing.", Toast.LENGTH_SHORT).show();
                if (dialog != null) dialog.dismiss();
                return;
            }
            reference.child(recipe.getId()).setValue(recipe).addOnCompleteListener(task -> {
                if (dialog != null) dialog.dismiss();
                if (task.isSuccessful()) {
                    Toast.makeText(AddRecipeActivity.this, "Recipe Updated Successfully", Toast.LENGTH_SHORT).show();
                    finish();
                } else {
                    Log.e("AddRecipeActivity", "Error updating recipe: " + task.getException().getMessage());
                    Toast.makeText(AddRecipeActivity.this, "Error in updating recipe", Toast.LENGTH_SHORT).show();
                }
            });
        } else {
            String id = reference.push().getKey();
            if (id != null) {
                recipe.setId(id); // Set the generated ID
                reference.child(id).setValue(recipe).addOnCompleteListener(task -> {
                    if (dialog != null) dialog.dismiss();
                    if (task.isSuccessful()) {
                        Toast.makeText(AddRecipeActivity.this, "Recipe Added Successfully", Toast.LENGTH_SHORT).show();
                        finish();
                    } else {
                        Log.e("AddRecipeActivity", "Error adding recipe: " + task.getException().getMessage());
                        Toast.makeText(AddRecipeActivity.this, "Error in adding recipe", Toast.LENGTH_SHORT).show();
                    }
                });
            } else {
                if (dialog != null) dialog.dismiss();
                Log.e("AddRecipeActivity", "Failed to generate push key for new recipe.");
                Toast.makeText(AddRecipeActivity.this, "Error generating recipe ID", Toast.LENGTH_SHORT).show();
            }
        }
    }
}