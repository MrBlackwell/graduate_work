package com.example.valentin.athena;

import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Matrix;
import android.graphics.Typeface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.view.LayoutInflater;
import android.view.View;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ScrollView;
import android.widget.Switch;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.AsyncHttpResponseHandler;
import com.loopj.android.http.BinaryHttpResponseHandler;
import com.loopj.android.http.JsonHttpResponseHandler;
import com.loopj.android.http.MySSLSocketFactory;
import com.loopj.android.http.RequestParams;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import cz.msebera.android.httpclient.Header;

import static com.example.valentin.athena.general.md5Custom;

public class SensorsActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {

    private int root;
    private String hash;
    private Context context = this;
    static private String sensors;
    static private String config;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sensors);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.addDrawerListener(toggle);
        toggle.syncState();

        NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        Intent intent = getIntent();
        hash = intent.getStringExtra("hash");
        root = Integer.parseInt(intent.getStringExtra("root"));
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
        findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
        findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
        findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
        findViewById(R.id.sensorsLayout).setVisibility(View.VISIBLE);
        if (root == 0) {
            findViewById(R.id.switchOfPenetration).setEnabled(false);
            findViewById(R.id.switchOfVibration).setEnabled(false);
            findViewById(R.id.switchOfWater).setEnabled(false);
            findViewById(R.id.switchOfSmoke).setEnabled(false);
            findViewById(R.id.switchOfMotion).setEnabled(false);
            findViewById(R.id.switchOfTemperatureAndHumidity).setEnabled(false);
            findViewById(R.id.switchOfRFID).setEnabled(false);

        }
        AsyncHttpClient httpClient = new AsyncHttpClient();
        httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
        RequestParams params = new RequestParams("hash", hash);
        httpClient.post(general.hostUrl + "senddata.php", params, new JsonHttpResponseHandler() {

            @Override
            public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                super.onSuccess(statusCode, headers, response);
                try {
                    if (Integer.parseInt(response.getString("result")) == 1) {
                        String sensors = response.getString("sensors");
                        String config = response.getString("config");
                        SensorsActivity.sensors = sensors;
                        SensorsActivity.config = config;
                        if (sensors.charAt(0) == '1') {
                            findViewById(R.id.constraintLayoutOfRFID).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfRFID).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(1) == '1') {
                            findViewById(R.id.constraintLayoutOfTemperatureAndHumidity).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfTemperatureAndHumidity).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(2) == '1') {
                            findViewById(R.id.constraintLayoutOfMotion).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfMotion).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(3) == '1') {
                            findViewById(R.id.constraintLayoutOfSmoke).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfSmoke).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(4) == '1') {
                            findViewById(R.id.constraintLayoutOfWater).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfWater).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(5) == '1') {
                            findViewById(R.id.constraintLayoutOfVibration).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfVibration).setBackgroundResource(R.drawable.frame);
                        }
                        if (sensors.charAt(6) == '1') {
                            findViewById(R.id.constraintLayoutOfPenetration).setBackgroundResource(R.drawable.frame_active);
                        } else {
                            findViewById(R.id.constraintLayoutOfPenetration).setBackgroundResource(R.drawable.frame);
                        }
                        if (config.charAt(0) == '1') {
                            ((Switch) findViewById(R.id.switchOfRFID)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfRFID)).setChecked(false);
                        }
                        if (config.charAt(1) == '1') {
                            ((Switch) findViewById(R.id.switchOfTemperatureAndHumidity)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfTemperatureAndHumidity)).setChecked(false);
                        }
                        if (config.charAt(2) == '1') {
                            ((Switch) findViewById(R.id.switchOfMotion)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfMotion)).setChecked(false);
                        }
                        if (config.charAt(3) == '1') {
                            ((Switch) findViewById(R.id.switchOfSmoke)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfSmoke)).setChecked(false);
                        }
                        if (config.charAt(4) == '1') {
                            ((Switch) findViewById(R.id.switchOfWater)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfWater)).setChecked(false);
                        }
                        if (config.charAt(5) == '1') {
                            ((Switch) findViewById(R.id.switchOfVibration)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfVibration)).setChecked(false);
                        }
                        if (config.charAt(6) == '1') {
                            ((Switch) findViewById(R.id.switchOfPenetration)).setChecked(true);
                        } else {
                            ((Switch) findViewById(R.id.switchOfPenetration)).setChecked(false);
                        }
                        String wettemp = "Температура: " + response.getString("temperature") + ". Влажность: " + response.getString("humidity");
                        ((TextView) findViewById(R.id.textTemperatureAndHumidity)).setText(wettemp);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }

            @Override
            public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                super.onFailure(statusCode, headers, responseString, throwable);
            }
        });
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.sensors, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.imageView2).setVisibility(View.INVISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.settingsLayout).setVisibility(View.VISIBLE);
            findViewById(R.id.edit_progressBar).setVisibility(View.VISIBLE);
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams();
            params.put("hash", hash);
            httpClient.post(general.hostUrl + "edituser_mobile.php", params, new JsonHttpResponseHandler() {

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        ((EditText) findViewById(R.id.edit_name)).setText(response.getString("name"));
                        ((EditText) findViewById(R.id.edit_login)).setText(response.getString("login"));
                        ((EditText) findViewById(R.id.edit_email)).setText(response.getString("email"));
                        findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }

                }

                @Override
                public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                    super.onFailure(statusCode, headers, responseString, throwable);
                }
            });
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();

        if (id == R.id.sensors) {
            findViewById(R.id.settingsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.sensorsLayout).setVisibility(View.VISIBLE);
            findViewById(R.id.imageView2).setVisibility(View.INVISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
            if (root == 0) {
                findViewById(R.id.switchOfPenetration).setEnabled(false);
                findViewById(R.id.switchOfVibration).setEnabled(false);
                findViewById(R.id.switchOfWater).setEnabled(false);
                findViewById(R.id.switchOfSmoke).setEnabled(false);
                findViewById(R.id.switchOfMotion).setEnabled(false);
                findViewById(R.id.switchOfTemperatureAndHumidity).setEnabled(false);
                findViewById(R.id.switchOfRFID).setEnabled(false);

            }
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams("hash", hash);
            //while (true) {
            httpClient.post(general.hostUrl + "senddata.php", params, new JsonHttpResponseHandler() {

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        if (Integer.parseInt(response.getString("result")) == 1) {
                            String sensors = response.getString("sensors");
                            String config = response.getString("config");
                            SensorsActivity.sensors = sensors;
                            SensorsActivity.config = config;
                            if (sensors.charAt(0) == '1') {
                                findViewById(R.id.constraintLayoutOfRFID).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfRFID).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(1) == '1') {
                                findViewById(R.id.constraintLayoutOfTemperatureAndHumidity).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfTemperatureAndHumidity).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(2) == '1') {
                                findViewById(R.id.constraintLayoutOfMotion).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfMotion).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(3) == '1') {
                                findViewById(R.id.constraintLayoutOfSmoke).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfSmoke).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(4) == '1') {
                                findViewById(R.id.constraintLayoutOfWater).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfWater).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(5) == '1') {
                                findViewById(R.id.constraintLayoutOfVibration).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfVibration).setBackgroundResource(R.drawable.frame);
                            }
                            if (sensors.charAt(6) == '1') {
                                findViewById(R.id.constraintLayoutOfPenetration).setBackgroundResource(R.drawable.frame_active);
                            } else {
                                findViewById(R.id.constraintLayoutOfPenetration).setBackgroundResource(R.drawable.frame);
                            }
                            if (config.charAt(0) == '1') {
                                ((Switch) findViewById(R.id.switchOfRFID)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfRFID)).setChecked(false);
                            }
                            if (config.charAt(1) == '1') {
                                ((Switch) findViewById(R.id.switchOfTemperatureAndHumidity)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfTemperatureAndHumidity)).setChecked(false);
                            }
                            if (config.charAt(2) == '1') {
                                ((Switch) findViewById(R.id.switchOfMotion)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfMotion)).setChecked(false);
                            }
                            if (config.charAt(3) == '1') {
                                ((Switch) findViewById(R.id.switchOfSmoke)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfSmoke)).setChecked(false);
                            }
                            if (config.charAt(4) == '1') {
                                ((Switch) findViewById(R.id.switchOfWater)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfWater)).setChecked(false);
                            }
                            if (config.charAt(5) == '1') {
                                ((Switch) findViewById(R.id.switchOfVibration)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfVibration)).setChecked(false);
                            }
                            if (config.charAt(6) == '1') {
                                ((Switch) findViewById(R.id.switchOfPenetration)).setChecked(true);
                            } else {
                                ((Switch) findViewById(R.id.switchOfPenetration)).setChecked(false);
                            }
                            String wettemp = "Температура: " + response.getString("temperature") + ". Влажность: " + response.getString("humidity");
                            ((TextView) findViewById(R.id.textTemperatureAndHumidity)).setText(wettemp);
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }

                @Override
                public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                    super.onFailure(statusCode, headers, responseString, throwable);
                }
            });
            //  }
        } else if (id == R.id.graphic) {
            findViewById(R.id.settingsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.progressBar3).setVisibility(View.VISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
            final AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams("hash", hash);
            final String[] allowedTypes = new String[]{".*"};
            httpClient.post(general.hostUrl + "draw_graph_mobile.php", params, new JsonHttpResponseHandler() {

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        if (Integer.parseInt(response.getString("result")) == 1) {
                            String name = response.getString("name");
                            httpClient.post(general.hostUrl + name, null, new BinaryHttpResponseHandler(allowedTypes) {
                                @Override
                                public void onSuccess(int statusCode, Header[] headers, byte[] binaryData) {
                                    ImageView imageView = (ImageView) findViewById(R.id.imageView2);
                                    Bitmap bmp = BitmapFactory.decodeByteArray(binaryData, 0, binaryData.length);
                                    Matrix matrix = new Matrix();
                                    matrix.postRotate(90);
                                    bmp = Bitmap.createBitmap(bmp, 0, 0, bmp.getWidth(), bmp.getHeight(), matrix, true);
                                    imageView.setImageBitmap(bmp);
                                    findViewById(R.id.imageView2).setVisibility(View.VISIBLE);
                                    findViewById(R.id.progressBar3).setVisibility(View.INVISIBLE);

                                }

                                @Override
                                public void onFailure(int statusCode, Header[] headers, byte[] binaryData, Throwable error) {

                                }
                            });
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }

                @Override
                public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                    super.onFailure(statusCode, headers, responseString, throwable);
                }
            });
        } else if (id == R.id.logs) {
            findViewById(R.id.settingsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.imageView2).setVisibility(View.INVISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.VISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.progressBar3).setVisibility(View.VISIBLE);
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams();
            params.put("hash", hash);
            httpClient.post(general.hostUrl + "getlog_mobile.php", params, new AsyncHttpResponseHandler() {
                @Override
                public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {
                    findViewById(R.id.progressBar3).setVisibility(View.INVISIBLE);
                    ((TextView) findViewById(R.id.textLog)).setText(new String(responseBody));
                }

                @Override
                public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

                }
            });
            ((Button) findViewById(R.id.requestDate)).setText("Запросить");
        } else if (id == R.id.users) {
            findViewById(R.id.settingsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.imageView2).setVisibility(View.INVISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.VISIBLE);
            findViewById(R.id.progressBar3).setVisibility(View.VISIBLE);
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams();
            params.put("hash", hash);
            httpClient.post(general.hostUrl + "getuser_mobile.php", params, new JsonHttpResponseHandler() {

                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONArray response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        JSONObject first = response.getJSONObject(0);
                        if (first.getInt("result") == 1) {
                            TableLayout table = (TableLayout) findViewById(R.id.tableUser);

                            LayoutInflater layoutInflater = LayoutInflater.from(context);

                            int childCount = table.getChildCount();

                            // Remove all rows except the first one
                            if (childCount > 1) {
                                table.removeViews(1, childCount - 1);
                            }

                            for (int i = 1; i < response.length(); i++) {
                                JSONObject json = response.getJSONObject(i);

                                TableRow row = (TableRow) layoutInflater.inflate(R.layout.tablerow, null);
                                TextView FIO = (TextView) row.findViewById(R.id.tableFIO);
                                FIO.setText(json.getString("FIO"));
                                TextView root = (TextView) row.findViewById(R.id.tableRoot);
                                if (json.getInt("root") == 1) {
                                    root.setText(getResources().getString(R.string.admin));
                                } else {
                                    root.setText(getResources().getString(R.string.user));
                                }
                                Button button = (Button) row.findViewById(R.id.tableDelete);
                                button.setTag(json.getInt("id"));
                                findViewById(R.id.progressBar3).setVisibility(View.INVISIBLE);
                                table.addView(row);
                            }
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }

                }

                @Override
                public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                    super.onFailure(statusCode, headers, responseString, throwable);
                }
            });
        } else if (id == R.id.exit) {
            findViewById(R.id.settingsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.sensorsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.imageView2).setVisibility(View.INVISIBLE);
            findViewById(R.id.logsLayout).setVisibility(View.INVISIBLE);
            findViewById(R.id.usersLayout).setVisibility(View.INVISIBLE);
            SharedPreferences sharedPreferences = context.getSharedPreferences("Authorization", MODE_PRIVATE);
            SharedPreferences.Editor editor = sharedPreferences.edit();
            editor.putString("hash", "");
            editor.apply();
            Intent intent = new Intent(this, LoginActivity.class);
            startActivity(intent);
            finish();
        }

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }

    public void switchClick(View view) {
        findViewById(R.id.progressBar3).setVisibility(View.VISIBLE);
        String config = "";
        if (((Switch) findViewById(R.id.switchOfRFID)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfTemperatureAndHumidity)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfMotion)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfSmoke)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfWater)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfVibration)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        if (((Switch) findViewById(R.id.switchOfPenetration)).isChecked()) {
            config += '1';
        } else {
            config += '0';
        }
        AsyncHttpClient httpClient = new AsyncHttpClient();
        httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
        RequestParams params = new RequestParams();
        params.put("config", Integer.parseInt(config, 2));
        params.put("hash", hash);
        httpClient.post(general.hostUrl + "senddata.php", params, new AsyncHttpResponseHandler() {
            @Override
            public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {

            }

            @Override
            public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

            }
        });
        findViewById(R.id.progressBar3).setVisibility(View.INVISIBLE);
    }

    public void requestDateLog(View view) {
        String date = ((EditText) findViewById(R.id.inputDate)).getText().toString();
        if (!date.isEmpty()) {
            if ((date.length() == 10)) {
                if ((date.charAt(2) == '.') && (date.charAt(5) == '.')) {
                    ((TextView) findViewById(R.id.textLog)).setText(date);
                    AsyncHttpClient httpClient = new AsyncHttpClient();
                    httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
                    RequestParams params = new RequestParams();
                    params.put("hash", hash);
                    params.put("date", date);
                    httpClient.post(general.hostUrl + "getlog_mobile.php", params, new AsyncHttpResponseHandler() {
                        @Override
                        public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {
                            ((TextView) findViewById(R.id.textLog)).setText(new String(responseBody));
                        }

                        @Override
                        public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

                        }
                    });
                    ((Button) findViewById(R.id.requestDate)).setText("Текущий лог");
                    ((EditText) findViewById(R.id.inputDate)).setText("");
                } else {
                    AsyncHttpClient httpClient = new AsyncHttpClient();
                    httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
                    RequestParams params = new RequestParams();
                    params.put("hash", hash);
                    httpClient.post(general.hostUrl + "getlog_mobile.php", params, new AsyncHttpResponseHandler() {
                        @Override
                        public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {
                            ((TextView) findViewById(R.id.textLog)).setText(new String(responseBody));
                        }

                        @Override
                        public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

                        }
                    });
                    ((Button) findViewById(R.id.requestDate)).setText("Запросить");
                    ((EditText) findViewById(R.id.inputDate)).setText("");
                }
            } else {
                AsyncHttpClient httpClient = new AsyncHttpClient();
                httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
                RequestParams params = new RequestParams();
                params.put("hash", hash);
                ((TextView) findViewById(R.id.textLog)).setText(params.toString());
                httpClient.post(general.hostUrl + "getlog_mobile.php", params, new AsyncHttpResponseHandler() {
                    @Override
                    public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {
                        ((TextView) findViewById(R.id.textLog)).setText(new String(responseBody));
                    }

                    @Override
                    public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

                    }
                });
                ((Button) findViewById(R.id.requestDate)).setText("Запросить");
                ((EditText) findViewById(R.id.inputDate)).setText("");
            }
        } else {
            AsyncHttpClient httpClient = new AsyncHttpClient();
            httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams();
            params.put("hash", hash);
            ((TextView) findViewById(R.id.textLog)).setText(params.toString());
            httpClient.post(general.hostUrl + "getlog_mobile.php", params, new AsyncHttpResponseHandler() {
                @Override
                public void onSuccess(int statusCode, Header[] headers, byte[] responseBody) {
                    ((TextView) findViewById(R.id.textLog)).setText(new String(responseBody));
                }

                @Override
                public void onFailure(int statusCode, Header[] headers, byte[] responseBody, Throwable error) {

                }
            });
            ((Button) findViewById(R.id.requestDate)).setText("Запросить");
            ((EditText) findViewById(R.id.inputDate)).setText("");
        }
    }

    public void deleteUser(View view) {
        Button button = (Button) view;
        int id = Integer.parseInt(button.getTag().toString());
        AsyncHttpClient httpClient = new AsyncHttpClient();
        httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
        RequestParams params = new RequestParams();
        params.put("hash", hash);
        params.put("id", id);
        httpClient.post(general.hostUrl + "getuser_mobile.php", params, new JsonHttpResponseHandler() {

            public void onSuccess(int statusCode, Header[] headers, JSONArray response) {
                super.onSuccess(statusCode, headers, response);
                try {
                    JSONObject first = response.getJSONObject(0);
                    if (first.getInt("result") == 1) {
                        TableLayout table = (TableLayout) findViewById(R.id.tableUser);

                        int childCount = table.getChildCount();

                        // Remove all rows except the first one
                        if (childCount > 1) {
                            table.removeViews(1, childCount - 1);
                        }

                        LayoutInflater layoutInflater = LayoutInflater.from(context);

                        for (int i = 1; i < response.length(); i++) {
                            JSONObject json = response.getJSONObject(i);

                            TableRow row = (TableRow) layoutInflater.inflate(R.layout.tablerow, null);
                            TextView FIO = (TextView) row.findViewById(R.id.tableFIO);
                            FIO.setText(json.getString("FIO"));
                            TextView root = (TextView) row.findViewById(R.id.tableRoot);
                            if (json.getInt("root") == 1) {
                                root.setText(getResources().getString(R.string.admin));
                            } else {
                                root.setText(getResources().getString(R.string.user));
                            }
                            Button button = (Button) row.findViewById(R.id.tableDelete);
                            button.setTag(json.getInt("id"));
                            findViewById(R.id.progressBar3).setVisibility(View.INVISIBLE);
                            table.addView(row);
                        }
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }

            @Override
            public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                super.onFailure(statusCode, headers, responseString, throwable);
            }
        });
    }

    public void addUserClick(View view) {
        Intent intent = new Intent(this, AddUserActivity.class);
        intent.putExtra("hash", hash);
        intent.putExtra("sensors", sensors);
        intent.putExtra("config", config);
        startActivity(intent);
    }

    public void editButtonClick(View view) {
        findViewById(R.id.edit_progressBar).setVisibility(View.VISIBLE);
        String name = ((EditText) findViewById(R.id.edit_name)).getText().toString();
        String email = ((EditText) findViewById(R.id.edit_email)).getText().toString();
        String login = ((EditText) findViewById(R.id.edit_login)).getText().toString();
        String password = ((EditText) findViewById(R.id.edit_password)).getText().toString();
        String repeatpassword = ((EditText) findViewById(R.id.edit_password_repeat)).getText().toString();
        if (password.equals(repeatpassword)) {
            if (!((name.equals("")) && (email.equals("")) && (login.equals("")) && (password.equals("")))) {
                if ((login.length() > 8) && (login.length() < 16)) {
                    if (((password.length() > 8) && !password.isEmpty()) || (password.isEmpty())) {
                        password = md5Custom(md5Custom(password));
                        AsyncHttpClient httpClient = new AsyncHttpClient();
                        httpClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
                        RequestParams params = new RequestParams();
                        params.put("hash", hash);
                        params.put("edit", "1");
                        params.put("name", name);
                        params.put("email", email);
                        params.put("login", login);
                        params.put("password", password);
                        httpClient.post(general.hostUrl + "edituser_mobile.php", params, new JsonHttpResponseHandler() {

                            public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                                try {
                                    if (response.getInt("result") == 1) {
                                        findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                                        Toast toast = Toast.makeText(context, R.string.edit_complete, Toast.LENGTH_SHORT);
                                        toast.show();
                                    } else {
                                        findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                                        Toast toast = Toast.makeText(context, R.string.edit_write_error, Toast.LENGTH_SHORT);
                                        toast.show();
                                    }
                                } catch (JSONException e) {
                                    e.printStackTrace();
                                }
                            }

                            @Override
                            public void onFailure(int statusCode, Header[] headers, String responseString, Throwable throwable) {
                                super.onFailure(statusCode, headers, responseString, throwable);
                                findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                                Toast toast = Toast.makeText(context, R.string.edit_json_error, Toast.LENGTH_SHORT);
                                toast.show();
                            }
                        });
                    } else {
                        findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                        Toast toast = Toast.makeText(context, R.string.edit_password_length_error, Toast.LENGTH_SHORT);
                        toast.show();
                    }
                } else {
                    findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                    Toast toast = Toast.makeText(context, R.string.edit_login_length_error, Toast.LENGTH_SHORT);
                    toast.show();
                }
            } else {
                findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
                Toast toast = Toast.makeText(context, R.string.edit_empty_error, Toast.LENGTH_SHORT);
                toast.show();
            }
        } else {
            findViewById(R.id.edit_progressBar).setVisibility(View.INVISIBLE);
            Toast toast = Toast.makeText(context, R.string.edit_password_error, Toast.LENGTH_SHORT);
            toast.show();
        }
    }
}