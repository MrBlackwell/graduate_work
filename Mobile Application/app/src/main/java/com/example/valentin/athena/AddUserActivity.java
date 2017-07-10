package com.example.valentin.athena;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.TextView;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.JsonHttpResponseHandler;
import com.loopj.android.http.MySSLSocketFactory;
import com.loopj.android.http.RequestParams;

import org.json.JSONException;
import org.json.JSONObject;

import cz.msebera.android.httpclient.Header;

import static com.example.valentin.athena.general.md5Custom;

public class AddUserActivity extends AppCompatActivity {

    private String hash;
    private String sensors;
    private String config;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_user);
        Intent intent = getIntent();
        hash = intent.getStringExtra("hash");
        sensors = intent.getStringExtra("sensors");
        config = intent.getStringExtra("config");
    }

    @Override
    protected void onStop() {
        super.onStop();
        Intent intent = new Intent(this, BackgroundIntentService.class);
        intent.putExtra("hash", hash);
        intent.putExtra("config", config);
        intent.putExtra("sensors", sensors);
        startService(intent);
    }

    @Override
    protected void onStart() {
        super.onStart();
        stopService(new Intent(this, BackgroundIntentService.class));
    }

    public void addButtonClick(View view) {
        findViewById(R.id.add_progressBar).setVisibility(View.VISIBLE);
        String name = ((EditText)findViewById(R.id.add_name)).getText().toString();
        String email = ((EditText)findViewById(R.id.add_email)).getText().toString();
        String login = ((EditText)findViewById(R.id.add_login)).getText().toString();
        String password = ((EditText)findViewById(R.id.add_password)).getText().toString();
        final String repeatpassword = ((EditText)findViewById(R.id.add_password_repeat)).getText().toString();
        String card = ((EditText)findViewById(R.id.add_card)).getText().toString();
        if(password.equals(repeatpassword)) {
            if((login.length() > 8) && (login.length() < 16)) {
                if(password.length() > 8) {
                    if ((!name.equals("")) && (!email.equals("")) && (!login.equals("")) && (!password.equals(""))
                            && (!card.equals(""))) {
                        card = md5Custom(card);
                        password = md5Custom(md5Custom(password));
                        int admin;
                        if (((CheckBox) findViewById(R.id.add_admin)).isChecked()) {
                            admin = 1;
                        } else {
                            admin = 0;
                        }
                        AsyncHttpClient httpClient = new AsyncHttpClient();
                        httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
                        RequestParams params = new RequestParams();
                        params.put("hash", hash);
                        params.put("name", name);
                        params.put("email", email);
                        params.put("login", login);
                        params.put("password", password);
                        params.put("card", card);
                        params.put("admin", admin);
                        httpClient.post(general.hostUrl + "adduser_mobile.php", params, new JsonHttpResponseHandler() {

                            public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                                try {
                                    if (response.getInt("result") == 1) {
                                        ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_complete));
                                        findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                                        findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
                                    } else {
                                        ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_write_error));
                                        findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                                        findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
                                    }
                                } catch (JSONException e) {
                                    e.printStackTrace();
                                }
                            }

                            @Override
                            public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                                super.onFailure(statusCode, headers, responseString, throwable);
                                ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_json_error));
                                findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                                findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
                            }
                        });
                    } else {
                        ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_empty_error));
                        findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                        findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
                    }
                } else {
                    ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_password_length_error));
                    findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                    findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
                }
            } else {
                ((TextView) findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_login_length_error));
                findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
                findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
            }
        } else {
            ((TextView)findViewById(R.id.add_error_text)).setText(getResources().getString(R.string.add_password_error));
            findViewById(R.id.add_error_text).setVisibility(View.VISIBLE);
            findViewById(R.id.add_progressBar).setVisibility(View.INVISIBLE);
        }
    }
}
