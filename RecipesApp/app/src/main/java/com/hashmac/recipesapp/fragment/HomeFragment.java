package com.hashmac.recipesapp.fragment;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.Toast; // Import Toast

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager; // Ensure correct LayoutManager

import com.google.firebase.database.DataSnapshot;
import com.google.firebase.database.DatabaseError;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.database.ValueEventListener;
import com.hashmac.recipesapp.AllRecipesActivity;
import com.hashmac.recipesapp.R;
import com.hashmac.recipesapp.adapters.HorizontalRecipeAdapter;
import com.hashmac.recipesapp.databinding.FragmentHomeBinding;
import com.hashmac.recipesapp.models.Recipe;

import java.util.ArrayList;
import java.util.Collections; // Import Collections for shuffle
import java.util.List;
import java.util.Objects;

public class HomeFragment extends Fragment {

    private FragmentHomeBinding binding;
    private HorizontalRecipeAdapter popularAdapter;
    private HorizontalRecipeAdapter favouriteAdapter;
    private DatabaseReference recipesRef;
    private ValueEventListener recipesListener;


    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentHomeBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        setupRecyclerViews();
        setupSearch();
        setupSeeAllListeners();
        attachFirebaseListener(); // Start listening when view is created
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        detachFirebaseListener(); // Stop listening when view is destroyed
        binding = null; // Clean up binding
    }

    private void setupRecyclerViews() {
        // Popular Recipes RecyclerView
        binding.rvPopulars.setLayoutManager(new LinearLayoutManager(getContext(), LinearLayoutManager.HORIZONTAL, false));
        popularAdapter = new HorizontalRecipeAdapter();
        binding.rvPopulars.setAdapter(popularAdapter);

        // Favourite Meal RecyclerView
        binding.rvFavouriteMeal.setLayoutManager(new LinearLayoutManager(getContext(), LinearLayoutManager.HORIZONTAL, false));
        favouriteAdapter = new HorizontalRecipeAdapter();
        binding.rvFavouriteMeal.setAdapter(favouriteAdapter);
    }

    private void setupSearch() {
        binding.etSearch.setOnEditorActionListener((textView, actionId, keyEvent) -> {
            if (actionId == EditorInfo.IME_ACTION_SEARCH) {
                performSearch();
                // Optional: Hide keyboard after search
                // InputMethodManager imm = (InputMethodManager) requireActivity().getSystemService(Context.INPUT_METHOD_SERVICE);
                // imm.hideSoftInputFromWindow(binding.etSearch.getWindowToken(), 0);
                return true;
            }
            return false;
        });
    }

    private void setupSeeAllListeners() {
        binding.tvSeeAllFavourite.setOnClickListener(view1 -> {
            if (getContext() == null) return;
            Intent intent = new Intent(requireContext(), AllRecipesActivity.class);
            // Note: "favourite" type might be confusing here as it's just showing random recipes
            // Maybe change type to "all" or create a specific type?
            // For now, keeping it as is, but it doesn't reflect actual favourites from Room.
            intent.putExtra("type", "all"); // Changed type to 'all' for clarity
            intent.putExtra("title", "Featured Recipes"); // Add a title maybe
            startActivity(intent);
        });

        binding.tvSeeAllPopulars.setOnClickListener(view1 -> {
            if (getContext() == null) return;
            Intent intent = new Intent(requireContext(), AllRecipesActivity.class);
            intent.putExtra("type", "all"); // Use "all" to show all recipes
            intent.putExtra("title", "Popular Recipes");
            startActivity(intent);
        });
    }


    private void performSearch() {
        if (getContext() == null || binding == null || binding.etSearch.getText() == null) return;
        String query = binding.etSearch.getText().toString().trim();
        if (query.isEmpty()) {
            Toast.makeText(getContext(), "Please enter a search term", Toast.LENGTH_SHORT).show();
            return;
        }
        Intent intent = new Intent(requireContext(), AllRecipesActivity.class);
        intent.putExtra("type", "search");
        intent.putExtra("query", query);
        startActivity(intent);
    }

    private void attachFirebaseListener() {
        // Detach existing listener first if any
        detachFirebaseListener();

        recipesRef = FirebaseDatabase.getInstance().getReference("Recipes");
        recipesListener = recipesRef.addValueEventListener(new ValueEventListener() {
            @Override
            public void onDataChange(@NonNull DataSnapshot snapshot) {
                if (binding == null || getContext() == null) return; // Check if fragment is still alive

                List<Recipe> recipes = new ArrayList<>();
                for (DataSnapshot dataSnapshot : snapshot.getChildren()) {
                    Recipe recipe = dataSnapshot.getValue(Recipe.class);
                    // Basic validation: Ensure essential fields are present if needed
                    if (recipe != null && recipe.getId() != null && recipe.getName() != null) {
                        recipes.add(recipe);
                    } else {
                        Log.w("HomeFragment", "Skipping invalid recipe data: " + dataSnapshot.getKey());
                    }
                }
                Log.d("HomeFragment", "Fetched " + recipes.size() + " recipes from Firebase.");

                // Now call the methods to populate adapters, they handle empty lists internally
                loadPopularRecipes(recipes);
                loadFavouriteRecipes(recipes); // Assuming 'favourite' means featured/random here
            }

            @Override
            public void onCancelled(@NonNull DatabaseError error) {
                Log.e("HomeFragment", "Firebase Recipe Load Error: " + error.getMessage());
                if (getContext() != null) { // Check context before showing Toast
                    Toast.makeText(getContext(), "Failed to load recipes: " + error.getMessage(), Toast.LENGTH_LONG).show();
                }
                // Clear adapters on error maybe?
                if(popularAdapter != null) popularAdapter.setRecipeList(new ArrayList<>());
                if(favouriteAdapter != null) favouriteAdapter.setRecipeList(new ArrayList<>());
            }
        });
    }

    private void detachFirebaseListener() {
        if (recipesRef != null && recipesListener != null) {
            recipesRef.removeEventListener(recipesListener);
            recipesRef = null;
            recipesListener = null;
            Log.d("HomeFragment", "Firebase listener detached.");
        }
    }

    // Corrected loadPopularRecipes
    private void loadPopularRecipes(List<Recipe> recipes) {
        if (popularAdapter == null) {
            Log.w("HomeFragment", "Popular adapter is null");
            return; // Adapter not ready
        }

        List<Recipe> popularRecipes = new ArrayList<>();
        if (recipes != null && !recipes.isEmpty()) { // *** CHECK IF LIST IS NOT EMPTY ***
            // Use Collections.shuffle for better random selection
            List<Recipe> shuffledRecipes = new ArrayList<>(recipes); // Create a copy
            Collections.shuffle(shuffledRecipes);

            int count = Math.min(5, shuffledRecipes.size()); // Get up to 5 recipes, or fewer if not enough exist
            for (int i = 0; i < count; i++) {
                popularRecipes.add(shuffledRecipes.get(i));
            }
            Log.d("HomeFragment", "Loaded " + popularRecipes.size() + " popular recipes.");
        } else {
            Log.d("HomeFragment", "No recipes available to load for popular section.");
        }
        // Always update the adapter, even with an empty list to clear previous items
        popularAdapter.setRecipeList(popularRecipes);
    }


    // Corrected loadFavouriteRecipes (assuming it shows random/featured, not actual Room favourites)
    private void loadFavouriteRecipes(List<Recipe> recipes) {
        if (favouriteAdapter == null) {
            Log.w("HomeFragment", "Favourite adapter is null");
            return; // Adapter not ready
        }

        List<Recipe> featuredRecipes = new ArrayList<>(); // Renamed for clarity
        if (recipes != null && !recipes.isEmpty()) { // *** CHECK IF LIST IS NOT EMPTY ***
            // Use Collections.shuffle for better random selection
            List<Recipe> shuffledRecipes = new ArrayList<>(recipes); // Create a copy
            Collections.shuffle(shuffledRecipes);

            int count = Math.min(5, shuffledRecipes.size()); // Get up to 5 recipes
            for (int i = 0; i < count; i++) {
                // Simple check to avoid adding the *exact same* recipes as popular (if list is small)
                // This is a basic attempt, might need refinement if list size is exactly 5 or less
                if (popularAdapter.getItemCount() < 5 || !shuffledRecipes.get(i).getId().equals(popularAdapter.recipeList.get(i % popularAdapter.getItemCount()).getId())) {
                    featuredRecipes.add(shuffledRecipes.get(i));
                }
                // Ensure we still try to get 5 distinct ones if possible, even if some overlap with popular
                if (featuredRecipes.size() >= count) break;
            }
            // If after the check we don't have enough, fill remaining slots (could be duplicates of popular if list < 10)
            int currentSize = featuredRecipes.size();
            if (currentSize < count) {
                for (int i = 0; i < shuffledRecipes.size() && featuredRecipes.size() < count; i++){
                    // Add if not already added
                    boolean alreadyAdded = false;
                    for(Recipe r : featuredRecipes) {
                        if (r.getId().equals(shuffledRecipes.get(i).getId())) {
                            alreadyAdded = true;
                            break;
                        }
                    }
                    if (!alreadyAdded) {
                        featuredRecipes.add(shuffledRecipes.get(i));
                    }
                }
            }


            Log.d("HomeFragment", "Loaded " + featuredRecipes.size() + " favourite/featured recipes.");
        } else {
            Log.d("HomeFragment", "No recipes available to load for favourite/featured section.");
        }
        // Always update the adapter
        favouriteAdapter.setRecipeList(featuredRecipes);
    }
}