package com.example.myapplication.models;

public class User {
    private int id;
    private String nom_u;
    private String prenom_u;
    private String email_u;
    private String role;
    private String matricule;

    public User(int id, String nom_u, String prenom_u, String email_u, String role, String matricule) {
        this.id = id;
        this.nom_u = nom_u;
        this.prenom_u = prenom_u;
        this.email_u = email_u;
        this.role = role;
        this.matricule = matricule;
    }

    public int getId() {
        return id;
    }
    public String getNomU() { return nom_u;}
    public String getPrenomU() { return prenom_u;}
    public String getEmailU() { return email_u;}
    public String getFullName() { return prenom_u + " " + nom_u; }

    
}