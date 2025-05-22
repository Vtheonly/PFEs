
package com.example.myapplication.models; 

public class Book {
    private int ID_Livre;
    private String Titre;
    private String Auteur;
    private String Categorie;
    private String Statut_Livre;
    private String ImageBase64; 

    public Book(int ID_Livre, String Titre, String Auteur, String Categorie, String Statut_Livre, String ImageBase64) {
        this.ID_Livre = ID_Livre;
        this.Titre = Titre;
        this.Auteur = Auteur;
        this.Categorie = Categorie;
        this.Statut_Livre = Statut_Livre;
        this.ImageBase64 = ImageBase64; 
    }

    public int getID_Livre() {
        return ID_Livre;
    }

    public String getTitre() {
        return Titre;
    }

    public String getAuteur() {
        return Auteur;
    }

    public String getCategorie() {
        return Categorie;
    }

    public String getStatut_Livre() {
        return Statut_Livre;
    }

    public String getImageBase64() { 
        return ImageBase64;
    }

    @Override
    public String toString() {
        return Titre + " by " + Auteur;
    }
}