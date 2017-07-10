package com.example.valentin.athena;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.AsyncHttpResponseHandler;
import com.loopj.android.http.JsonHttpResponseHandler;
import com.loopj.android.http.MySSLSocketFactory;
import com.loopj.android.http.RequestParams;

import org.json.JSONException;
import org.json.JSONObject;


import cz.msebera.android.httpclient.Header;

import static com.example.valentin.athena.general.md5Custom;

public class LoginActivity extends AppCompatActivity {

    final Context context = this;

    @Override
    protected void onStart() {
        super.onStart();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        SharedPreferences sharedPreferences = context.getSharedPreferences("Authorization", Context.MODE_PRIVATE);
        if(sharedPreferences.contains("hash")) {
            String hash = sharedPreferences.getString("hash", "Не считалось");
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams("hash", hash);
            httpClient.post(general.hostUrl + "mobile_authorization.php", params, new JsonHttpResponseHandler(){

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        if(response.getInt("result") == 1) {
                            String hash = response.getString("hash");
                            String root = response.getString("root");
                            findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);
                            SharedPreferences sharedPreferences = context.getSharedPreferences("Authorization", MODE_PRIVATE);
                            SharedPreferences.Editor editor = sharedPreferences.edit();
                            editor.putString("hash", hash);
                            editor.apply();
                            Intent intent = new Intent(context, SensorsActivity.class);
                            intent.putExtra("hash", hash);
                            intent.putExtra("root", root);
                            startActivity(intent);
                            finish();
                        }
                    } catch (JSONException ignored) {}

                }

                @Override
                public void onFailure(int statusCode, Header[] headers, Throwable throwable, JSONObject errorResponse) {
                    super.onFailure(statusCode, headers, throwable, errorResponse);
                    ((TextView)findViewById(R.id.errorLog)).setText(R.string.errorconnection);
                    findViewById(R.id.errorLog).setVisibility(View.VISIBLE);
                }
            });
        }
    }

    public void authorization(View view) {
        findViewById(R.id.submit).setVisibility(View.INVISIBLE);
        findViewById(R.id.progressBar).setVisibility(View.VISIBLE);
        String login = ((EditText)findViewById(R.id.login)).getText().toString();
        String password = ((EditText)findViewById(R.id.password)).getText().toString();
        if((!login.equals("")) && (!password.equals(""))){
            String passwordHash = md5Custom(md5Custom(password));
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams();
            params.put("login", login);
            params.put("password", passwordHash);
            httpClient.post(general.hostUrl + "mobile_authorization.php", params, new JsonHttpResponseHandler(){

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        if(response.getInt("result") == 1) {
                            String hash = response.getString("hash");
                            String root = response.getString("root");
                            findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);
                            SharedPreferences sharedPreferences = context.getSharedPreferences("Authorization", MODE_PRIVATE);
                            SharedPreferences.Editor editor = sharedPreferences.edit();
                            editor.putString("hash", hash);
                            editor.apply();
                            Intent intent = new Intent(context, SensorsActivity.class);
                            intent.putExtra("hash", hash);
                            intent.putExtra("root", root);
                            startActivity(intent);
                            finish();
                        } else {
                            ((TextView)findViewById(R.id.errorLog)).setText(R.string.errordata);
                            findViewById(R.id.errorLog).setVisibility(View.VISIBLE);
                            findViewById(R.id.submit).setVisibility(View.VISIBLE);
                            findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);

                        }
                    } catch (JSONException e) {
                        ((TextView)findViewById(R.id.errorLog)).setText(R.string.jsonerror);
                        findViewById(R.id.errorLog).setVisibility(View.VISIBLE);
                        findViewById(R.id.submit).setVisibility(View.VISIBLE);
                        findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);
                    }

                }

                @Override
                public void onFailure(int statusCode, Header[] headers, Throwable throwable, JSONObject errorResponse) {
                    super.onFailure(statusCode, headers, throwable, errorResponse);
                    ((TextView)findViewById(R.id.errorLog)).setText(R.string.errorconnection);
                    findViewById(R.id.errorLog).setVisibility(View.VISIBLE);
                    findViewById(R.id.submit).setVisibility(View.VISIBLE);
                    findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);
                }
            });
        } else {
            ((TextView)findViewById(R.id.errorLog)).setText(R.string.fillerror);
            findViewById(R.id.errorLog).setVisibility(View.VISIBLE);
            findViewById(R.id.submit).setVisibility(View.VISIBLE);
            findViewById(R.id.progressBar).setVisibility(View.INVISIBLE);
        }
    }
}

