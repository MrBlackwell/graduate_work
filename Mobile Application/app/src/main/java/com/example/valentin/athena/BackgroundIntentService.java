package com.example.valentin.athena;

import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Intent;
import android.content.Context;
import android.content.res.Resources;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.provider.Settings;
import android.util.Log;
import android.widget.Switch;
import android.widget.TextView;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.AsyncHttpResponseHandler;
import com.loopj.android.http.JsonHttpResponseHandler;
import com.loopj.android.http.MySSLSocketFactory;
import com.loopj.android.http.RequestParams;
import com.loopj.android.http.SyncHttpClient;

import org.json.JSONException;
import org.json.JSONObject;

import cz.msebera.android.httpclient.Header;

/**
 * An {@link IntentService} subclass for handling asynchronous task requests in
 * a service on a separate handler thread.
 * <p>
 * TODO: Customize class - update intent actions, extra parameters and static
 * helper methods.
 */
public class BackgroundIntentService extends IntentService {
    public static final String LOG_TAG = "ExampleIntentService";
    private String hash;
    static private String config;
    static private String sensors;
    private NotificationManager notificationManager;
    private Context context = this;

    private final AsyncHttpClient aClient = new SyncHttpClient();

    public BackgroundIntentService() {
        super("ExampleIntentService");
    }

    @Override
    public void onStart(Intent intent, int startId) {
        Log.d(LOG_TAG, "onStart()");
        super.onStart(intent, startId);
        notificationManager = (NotificationManager) context
                .getSystemService(Context.NOTIFICATION_SERVICE);
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        hash = intent.getStringExtra("hash");
        config = intent.getStringExtra("config");
        sensors = intent.getStringExtra("sensors");
        while (true) {
            aClient.setSSLSocketFactory(MySSLSocketFactory.getFixedSocketFactory());
            RequestParams params = new RequestParams("hash", hash);
            aClient.post(general.hostUrl + "senddata.php", params, new JsonHttpResponseHandler() {
                @Override
                public void onSuccess(int statusCode, Header[] headers, JSONObject response) {
                    super.onSuccess(statusCode, headers, response);
                    try {
                        if (Integer.parseInt(response.getString("result")) == 1) {
                            String sensors = response.getString("sensors");
                            String config = response.getString("config");
                            if (!(BackgroundIntentService.sensors).equals(sensors)) {
                                sendNotificationAlarm(sensors);
                            }
                            if (!(BackgroundIntentService.config).equals(config)) {
                                sendNotificationState(config);
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
            try {
                java.util.concurrent.TimeUnit.SECONDS.sleep(30);
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
    }

    void sendNotificationAlarm(String sensors) {
        String text = "";
        Resources resources = context.getResources();

        char ch1;
        char ch2;
        ch1 = sensors.charAt(0);
        ch2 = (BackgroundIntentService.sensors).charAt(0);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_64) + "\r\n";
        }
        ch1 = sensors.charAt(1);
        ch2 = (BackgroundIntentService.sensors).charAt(1);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_32) + "\r\n";
        }
        ch1 = sensors.charAt(2);
        ch2 = (BackgroundIntentService.sensors).charAt(2);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_16) + "\r\n";
        }
        ch1 = sensors.charAt(3);
        ch2 = (BackgroundIntentService.sensors).charAt(3);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_8) + "\r\n";
        }
        ch1 = sensors.charAt(4);
        ch2 = (BackgroundIntentService.sensors).charAt(4);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_4) + "\r\n";
        }
        ch1 = sensors.charAt(5);
        ch2 = (BackgroundIntentService.sensors).charAt(5);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_2) + "\r\n";
        }
        ch1 = sensors.charAt(6);
        ch2 = (BackgroundIntentService.sensors).charAt(6);
        if ((ch1 == '1') && (ch1 != ch2)) {
            text += resources.getString(R.string.service_alarm_1) + "\r\n";
        }

        if (text.equals("")) {
            text += resources.getString(R.string.service_alarm_0);
        }

        BackgroundIntentService.sensors = sensors;

        Intent intent = new Intent(context, LoginActivity.class);
        PendingIntent pendingIntent = PendingIntent.getActivity(context, 0, intent, PendingIntent.FLAG_CANCEL_CURRENT);


        Notification.Builder builder = new Notification.Builder(context);

        builder.setContentIntent(pendingIntent)
                .setSmallIcon(R.drawable.icon)
                .setLargeIcon(BitmapFactory.decodeResource(resources, R.mipmap.ic_launcher))
                .setTicker(resources.getString(R.string.service_ticker))
                .setWhen(System.currentTimeMillis())
                .setAutoCancel(true)
                .setContentTitle(resources.getString(R.string.service_alarm_title))
                .setContentText(text)
                .setSound(Settings.System.DEFAULT_NOTIFICATION_URI)
                .setVibrate(new long[]{500, 500, 500})
                .setLights(Color.WHITE, 500, 500);

        Notification notification = builder.build();

        notification.flags |= Notification.FLAG_AUTO_CANCEL;

        notificationManager.notify(1, notification);
    }

    void sendNotificationState(String config) {
        Resources resources = getResources();

        String text = "";

        char ch1;
        char ch2;
        ch1 = config.charAt(0);
        ch2 = (BackgroundIntentService.config).charAt(0);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_64_on);
            } else {
                text += resources.getString(R.string.service_state_64_off);
            }
        }
        ch1 = config.charAt(1);
        ch2 = (BackgroundIntentService.config).charAt(1);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_32_on);
            } else {
                text += resources.getString(R.string.service_state_32_off);
            }
        }
        ch1 = config.charAt(2);
        ch2 = (BackgroundIntentService.config).charAt(2);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_16_on);
            } else {
                text += resources.getString(R.string.service_state_16_off);
            }
        }
        ch1 = config.charAt(3);
        ch2 = (BackgroundIntentService.config).charAt(3);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_8_on);
            } else {
                text += resources.getString(R.string.service_state_8_off);
            }
        }
        ch1 = config.charAt(4);
        ch2 = (BackgroundIntentService.config).charAt(4);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_4_on);
            } else {
                text += resources.getString(R.string.service_state_4_off);
            }
        }
        ch1 = config.charAt(5);
        ch2 = (BackgroundIntentService.config).charAt(5);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_2_on);
            } else {
                text += resources.getString(R.string.service_state_2_off);
            }
        }
        ch1 = config.charAt(6);
        ch2 = (BackgroundIntentService.config).charAt(6);
        if (ch1 != ch2) {
            if (ch1 == '1') {
                text += resources.getString(R.string.service_state_1_on);
            } else {
                text += resources.getString(R.string.service_state_1_off);
            }
        }

        BackgroundIntentService.config = config;

        Intent intent = new Intent(this, LoginActivity.class);
        PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_CANCEL_CURRENT);


        Notification.Builder builder = new Notification.Builder(this);

        builder.setContentIntent(pendingIntent)
                .setSmallIcon(R.drawable.icon)
                .setLargeIcon(BitmapFactory.decodeResource(resources, R.mipmap.ic_launcher))
                .setTicker(resources.getString(R.string.service_ticker))
                .setWhen(System.currentTimeMillis())
                .setAutoCancel(true)
                .setContentTitle(resources.getString(R.string.service_state_title))
                .setContentText(text)
                .setSound(Settings.System.DEFAULT_NOTIFICATION_URI)
                .setVibrate(new long[]{500, 500, 500})
                .setLights(Color.WHITE, 500, 500);

        Notification notification = builder.build();

        notificationManager.notify(2, notification);
    }
}
