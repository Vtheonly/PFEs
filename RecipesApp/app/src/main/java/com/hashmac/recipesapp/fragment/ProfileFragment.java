package com.hashmac.recipesapp.fragment;

import android.app.AlertDialog;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.GridLayoutManager;

import com.bumptech.glide.Glide;
// Firebase Storage imports removed
// import com.google.firebase.storage.FirebaseStorage;
// import com.google.firebase.storage.StorageReference;
// import com.google.firebase.storage.UploadTask;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseUser;
import com.google.firebase.database.DataSnapshot;
import com.google.firebase.database.DatabaseError;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.database.ValueEventListener;
import com.hashmac.recipesapp.LoginActivity; // Import LoginActivity for redirection
import com.hashmac.recipesapp.R;
import com.hashmac.recipesapp.SettingActivity;
import com.hashmac.recipesapp.adapters.RecipeAdapter;
import com.hashmac.recipesapp.databinding.FragmentProfileBinding;
import com.hashmac.recipesapp.models.Recipe;
import com.hashmac.recipesapp.models.User;
// PickImage imports removed
// import com.vansuita.pickimage.bundle.PickSetup;
// import com.vansuita.pickimage.dialog.PickImageDialog;


import java.io.ByteArrayOutputStream;
import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

public class ProfileFragment extends Fragment {

    private FragmentProfileBinding binding;
    private User user; // Holds the loaded user data
    private RecipeAdapter recipeAdapter;
    private DatabaseReference userRef;
    private ValueEventListener userValueListener;
    private DatabaseReference recipesRef;
    private ValueEventListener recipesValueListener;


    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentProfileBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        setupRecyclerView();

        if (FirebaseAuth.getInstance().getCurrentUser() == null) {
            showLoginRequiredDialog();
            // Disable profile specific buttons if not logged in
            binding.imgEditProfile.setVisibility(View.GONE);
            binding.imgEditCover.setVisibility(View.GONE);
            binding.btnSetting.setVisibility(View.GONE); // Or handle differently
        } else {
            init(); // Initialize listeners only if logged in
            loadProfile(); // Start loading profile data
        }
    }

    private void setupRecyclerView() {
        binding.rvProfile.setLayoutManager(new GridLayoutManager(getContext(), 2));
        recipeAdapter = new RecipeAdapter();
        binding.rvProfile.setAdapter(recipeAdapter);
        // Consider adding item decoration for spacing if needed
        // binding.rvProfile.addItemDecoration(...)
    }


    private void showLoginRequiredDialog() {
        if (getContext() == null) return; // Prevent crash if context is null
        new AlertDialog.Builder(getContext())
                .setTitle("Login Required")
                .setMessage("You need to login to view or manage your profile and recipes.")
                .setPositiveButton("Login", (dialog, which) -> {
                    // Redirect to Login Screen
                    Intent intent = new Intent(getActivity(), LoginActivity.class);
                    intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(intent);
                    if (getActivity() != null) {
                        getActivity().finish(); // Finish MainActivity if desired
                    }
                })
                .setNegativeButton("Cancel", (dialog, which) -> {
                    // Optional: Navigate to home or do nothing
                    dialog.dismiss();
                })
                .setCancelable(false) // Prevent dismissing by tapping outside
                .show();
    }


    @Override
    public void onResume() {
        super.onResume();
        // Reload recipes in onResume in case user adds/edits/deletes recipes
        // and comes back to this fragment
        if (FirebaseAuth.getInstance().getCurrentUser() != null) {
            loadUserRecipes();
        } else {
            // Clear the recycler view if user logs out
            if (recipeAdapter != null) {
                recipeAdapter.setRecipeList(new ArrayList<>());
            }
        }
    }

    @Override
    public void onPause() {
        super.onPause();
        // Detach listeners to prevent memory leaks and unnecessary background updates
        detachFirebaseListeners();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        detachFirebaseListeners(); // Ensure listeners are detached
        binding = null; // Release binding
    }

    private void detachFirebaseListeners() {
        if (userRef != null && userValueListener != null) {
            userRef.removeEventListener(userValueListener);
            userRef = null;
            userValueListener = null;
        }
        if (recipesRef != null && recipesValueListener != null) {
            recipesRef.removeEventListener(recipesValueListener);
            recipesRef = null;
            recipesValueListener = null;
        }
    }

    private void init() {
        // Hide image edit buttons as we removed Storage
        binding.imgEditProfile.setVisibility(View.GONE);
        binding.imgEditCover.setVisibility(View.GONE);

        // binding.imgEditProfile.setOnClickListener(v -> {
        //     PickImageDialog.build(new PickSetup()).show(requireActivity()).setOnPickResult(r -> {
        //         Log.e("ProfileFragment", "Profile PickResult: " + r.getUri());
        //         binding.imgProfile.setImageBitmap(r.getBitmap());
        //         // Removed uploadImage call
        //     }).setOnPickCancel(() -> Toast.makeText(requireContext(), "Cancelled", Toast.LENGTH_SHORT).show());
        // });

        // binding.imgEditCover.setOnClickListener(view ->
        //     PickImageDialog.build(new PickSetup()).show(requireActivity()).setOnPickResult(r -> {
        //         Log.e("ProfileFragment", "Cover PickResult: " + r.getUri());
        //         binding.imgCover.setImageBitmap(r.getBitmap());
        //         binding.imgCover.setScaleType(ImageView.ScaleType.CENTER_CROP);
        //         // Removed uploadCoverImage call
        //     }).setOnPickCancel(() -> Toast.makeText(requireContext(), "Cancelled", Toast.LENGTH_SHORT).show()));

        binding.btnSetting.setOnClickListener(view1 -> {
            if (getContext() != null) {
                startActivity(new Intent(requireContext(), SettingActivity.class));
            }
        });
    }

    // Removed uploadCoverImage method
    // Removed uploadImage method

    private void loadUserRecipes() {
        FirebaseUser currentUser = FirebaseAuth.getInstance().getCurrentUser();
        if (currentUser == null || getContext() == null) {
            Log.w("ProfileFragment", "User not logged in or context is null, cannot load recipes.");
            if(recipeAdapter != null) recipeAdapter.setRecipeList(new ArrayList<>()); // Clear list
            return;
        }
        String userId = currentUser.getUid();

        // Detach previous listener if exists
        if (recipesRef != null && recipesValueListener != null) {
            recipesRef.removeEventListener(recipesValueListener);
        }

        recipesRef = FirebaseDatabase.getInstance().getReference("Recipes");
        recipesValueListener = recipesRef.orderByChild("authorId").equalTo(userId)
                .addValueEventListener(new ValueEventListener() { // Use addValueEventListener for real-time updates
                    @Override
                    public void onDataChange(@NonNull DataSnapshot snapshot) {
                        if (binding == null) return; // Check if view is still available
                        List<Recipe> recipes = new ArrayList<>();
                        for (DataSnapshot dataSnapshot : snapshot.getChildren()) {
                            Recipe recipe = dataSnapshot.getValue(Recipe.class);
                            if (recipe != null) {
                                recipes.add(recipe);
                            }
                        }
                        if (recipeAdapter != null) {
                            recipeAdapter.setRecipeList(recipes);
                        }
                    }

                    @Override
                    public void onCancelled(@NonNull DatabaseError error) {
                        Log.e("ProfileFragment", "onCancelled loading recipes: " + error.getMessage());
                        if (getContext() != null) {
                            Toast.makeText(getContext(), "Failed to load recipes.", Toast.LENGTH_SHORT).show();
                        }
                    }
                });
    }

    private void loadProfile() {
        FirebaseUser currentUser = FirebaseAuth.getInstance().getCurrentUser();
        if (currentUser == null || getContext() == null) {
            Log.w("ProfileFragment", "User not logged in or context is null, cannot load profile.");
            // Optionally clear fields or show placeholder state
            binding.tvUserName.setText(R.string.user_name); // Placeholder text
            binding.tvEmail.setText("");
            Glide.with(requireContext()).load(R.drawable.ic_profile).into(binding.imgProfile);
            Glide.with(requireContext()).load(R.drawable.bg_default_recipe).into(binding.imgCover);
            return;
        }
        String userId = currentUser.getUid();

        // Detach previous listener if exists
        if (userRef != null && userValueListener != null) {
            userRef.removeEventListener(userValueListener);
        }

        userRef = FirebaseDatabase.getInstance().getReference("Users").child(userId);
        userValueListener = userRef.addValueEventListener(new ValueEventListener() { // Use add for real-time updates if needed, or keep single event
            @Override
            public void onDataChange(@NonNull DataSnapshot snapshot) {
                if (binding == null || getContext() == null) return; // Check if view/context is still valid

                user = snapshot.getValue(User.class); // Update the class member 'user'
                if (user != null) {
                    binding.tvUserName.setText(user.getName() != null ? user.getName() : "User Name");
                    binding.tvEmail.setText(user.getEmail() != null ? user.getEmail() : "email@example.com");

                    // Load static placeholder for profile image
                    Glide.with(requireContext())
                            .load(R.drawable.ic_profile) // Static profile placeholder
                            .centerCrop()
                            .placeholder(R.drawable.ic_profile)
                            .error(R.drawable.ic_profile) // Show placeholder on error
                            .into(binding.imgProfile);

                    // Load static placeholder for cover image
                    Glide.with(requireContext())
                            .load(R.drawable.bg_default_recipe) // Static cover placeholder
                            .centerCrop()
                            .placeholder(R.drawable.bg_default_recipe)
                            .error(R.drawable.bg_default_recipe) // Show placeholder on error
                            .into(binding.imgCover);
                } else {
                    Log.e("ProfileFragment", "User data is null for UID: " + userId);
                    // Handle case where user node doesn't exist or is null
                    binding.tvUserName.setText(R.string.user_name);
                    binding.tvEmail.setText("");
                    Glide.with(requireContext()).load(R.drawable.ic_profile).into(binding.imgProfile);
                    Glide.with(requireContext()).load(R.drawable.bg_default_recipe).into(binding.imgCover);
                }
            }

            @Override
            public void onCancelled(@NonNull DatabaseError error) {
                Log.e("ProfileFragment", "onCancelled loading profile: " + error.getMessage());
                if (getContext() != null) {
                    Toast.makeText(getContext(), "Failed to load profile.", Toast.LENGTH_SHORT).show();
                    // Optionally set placeholder values on error
                    binding.tvUserName.setText(R.string.user_name);
                    binding.tvEmail.setText("");
                }
            }
        });
    }
}