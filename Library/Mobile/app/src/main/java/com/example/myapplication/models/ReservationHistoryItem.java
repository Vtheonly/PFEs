package com.example.myapplication.models;

public class ReservationHistoryItem {
    private int idReservation;
    private int livreId;
    private String titreLivre;
    private String auteurLivre;
    private String categorieLivre;
    private String imageLivreBase64; 
    private String dateReservation;
    private String dateRetourPrevu;
    private String dateRetourEffectif; 
    private String statutReservation;

    
    public ReservationHistoryItem(int idReservation, int livreId, String titreLivre, String auteurLivre, String categorieLivre, String imageLivreBase64, String dateReservation, String dateRetourPrevu, String dateRetourEffectif, String statutReservation) {
        this.idReservation = idReservation;
        this.livreId = livreId;
        this.titreLivre = titreLivre;
        this.auteurLivre = auteurLivre;
        this.categorieLivre = categorieLivre;
        this.imageLivreBase64 = imageLivreBase64;
        this.dateReservation = dateReservation;
        this.dateRetourPrevu = dateRetourPrevu;
        this.dateRetourEffectif = dateRetourEffectif;
        this.statutReservation = statutReservation;
    }

    
    public int getIdReservation() { return idReservation; }
    public int getLivreId() { return livreId; }
    public String getTitreLivre() { return titreLivre; }
    public String getAuteurLivre() { return auteurLivre; }
    public String getCategorieLivre() { return categorieLivre; }
    public String getImageLivreBase64() { return imageLivreBase64; }
    public String getDateReservation() { return dateReservation; }
    public String getDateRetourPrevu() { return dateRetourPrevu; }
    public String getDateRetourEffectif() { return dateRetourEffectif; }
    public String getStatutReservation() { return statutReservation; }
}