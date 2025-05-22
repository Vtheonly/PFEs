package com.example.myapplication.utils;

public class Constants {

    public static final String BASE_URL = "http://10.0.2.2:8000/classes/api/";

    public static final String LOGIN_URL = BASE_URL + "api_user.php";


    public static final String API_LIVRE_URL = BASE_URL + "api_livre.php"; 


    public static final String REGISTER_URL = BASE_URL + "api_user.php";
    public static final String SUGGEST_BOOK_URL = BASE_URL + "api_suggestion.php";
    public static final String RESERVE_BOOK_URL = BASE_URL + "api_reservation.php";
    public static final String SEND_MESSAGE_URL = BASE_URL + "api_message.php";

  
    public static final String SHARED_PREF_NAME = "libraryAppSharedPref";
    public static final String KEY_USER_ID = "keyUserId";
    public static final String KEY_USER_NAME = "keyUserName";
    public static final String KEY_USER_EMAIL = "keyUserEmail";
    public static final String KEY_IS_LOGGED_IN = "keyIsLoggedIn";


    public static final String RESERVATION_HISTORY_URL = BASE_URL + "api_reservation_history.php?user_id=";

}