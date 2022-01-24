#include <WiFi.h>
#include <HTTPClient.h>

#define LED 25
#define WIFILED 26
#define ANALOG A4
#define BUTTON 27
#define SIZE 1000

//#define DEBUG

struct Registros{
  double temp;
  String date;
  String hour;
  boolean load = false;
};

const char *ssdi = "TOTALPLAY_9093DC";
const char *pass_ssdi = "CER4D2ZT1W";

int id_device = 11;

String urlLogin = "http://192.168.100.219/sistemaTemperatura/login.php";
String urlConfig = "http://192.168.100.219/sistemaTemperatura/config.php";
String urlCargar = "http://192.168.100.219/sistemaTemperatura/mandar_datos.php";
String headerCookie;

String user = "sensor";
String pass_user = "1234";

struct tm timeinfo;
int gmtOffset=0;
int delta_seg=3600, delta_temp=10;

int nextLoad = 0;
double lastTemp = 0;
double temperatura = 0;

Registros registro[SIZE];
int indicePush=0;
int indicePop=0;

boolean sessionState = false;
boolean loadDataState = false;
boolean wifiState = false;
boolean timeState = false;

void setup(){
  pinMode(LED, OUTPUT);
  pinMode(WIFILED, OUTPUT);
  pinMode(BUTTON, INPUT);
  digitalWrite(LED, LOW);
  digitalWrite(WIFILED, LOW);

  #ifdef DEBUG
  Serial.begin(9600);
  Serial.println("\nDEBUG");
  #endif

  wifiConfig();
  wifiState = true;
}

void loop(){
  if(WiFi.status()==WL_CONNECTED){
    digitalWrite(WIFILED, HIGH);
    wifiState = true;
  }else{
    digitalWrite(WIFILED, LOW);
    wifiState = false;
  }

  if(timeState){ 
    getLocalTime(&timeinfo);
    #ifdef DEBUG
    Serial.println(&timeinfo, "%A, %B %d %Y %H:%M:%S");
    #endif
  }

  if(loadDataState){
    temperatura = leerTemperatura();
    #ifdef DEBUG
    Serial.println("Temp: " + String(temperatura));
    Serial.println("Next time: " + String(nextLoad - (timeinfo.tm_hour*60*60 + timeinfo.tm_min*60 + timeinfo.tm_sec)));
    Serial.println("NextTemp: " + String(lastTemp-delta_temp) + " - " + String(lastTemp+delta_temp));
    #endif
    if(nextLoad <= (timeinfo.tm_hour*60*60 + timeinfo.tm_min*60 + timeinfo.tm_sec) || temperatura >= lastTemp+delta_temp || temperatura <= lastTemp-delta_temp){
      lastTemp = temperatura;
      if(nextLoad <= (timeinfo.tm_hour*60*60 + timeinfo.tm_min*60 + timeinfo.tm_sec)){
        nextLoad = (nextLoad + delta_seg) % 86400;
      }
      if(!registro[indicePush].load){
        #ifdef DEBUG
        Serial.println("Cargar Datos en " + String(indicePush));
        #endif
        registro[indicePush].temp = temperatura;
        registro[indicePush].date = String(timeinfo.tm_year+1900) + "-" + String(timeinfo.tm_mon+1) + "-" + String(timeinfo.tm_mday);
        registro[indicePush].hour = String(timeinfo.tm_hour) + ":" + String(timeinfo.tm_min) + ":" + String(timeinfo.tm_sec);
        registro[indicePush].load = true;
        indicePush++;
        if(indicePush == SIZE) indicePush = 0;
        blinkblink(1, 100);
      }
    }
    if(registro[indicePop].load && sessionState && wifiState){
      #ifdef DEBUG
      Serial.println("Enviar Datos de registro " + String(indicePop));
      #endif
      if(loadData(registro[indicePop].temp, registro[indicePop].date , registro[indicePop].hour)){
        registro[indicePop].load = false;
        indicePop++;
        if(indicePop == SIZE) indicePop = 0;
      }else{
        blinkblink(2, 100);
      }
    }
  }
  
  
  actionButton();
  
}

void actionButton(){
  int t = getButton(9, 2);
  #ifdef DEBUG
  Serial.println("Seg: " + String(t));
  #endif
  if(t>=2 && t<=3){
    if(WiFi.status()==WL_CONNECTED){
      if(sessionState){
        if(!loadConfig()){
          blinkblink(4, 250);
          loadDataState = false;
        }else{
          lastTemp = leerTemperatura();
          nextLoad = (timeinfo.tm_hour*60*60 + timeinfo.tm_min*60  + timeinfo.tm_sec) % 86400;
        }
      }
    }
  }else if(t>=5 && t<=6){
    if(wifiState){
      WiFi.disconnect(true);
      WiFi.mode(WIFI_OFF);
      wifiState = false;
      #ifdef DEBUG
      Serial.println("WiFi apagado");
      #endif
    }else{
      WiFi.begin(ssdi, pass_ssdi);
      wifiState = true;
      Serial.println("WiFi encendido");
    }
  }else if(t>=8 && t<=9){
    if(sessionState){
      if(!closeSession()){
        blinkblink(4, 250);
      }else{
        sessionState = false;
      }
    }else{
      if(!startSession()){
        blinkblink(4, 250);
      }else{
        sessionState = true;
      }
    }
  }
}

//editar para reutilizar y crear en la carga de configuración
boolean loadData(double temp, String fecha, String hora){
  digitalWrite(LED, HIGH);
  boolean estado = true;
  if(WiFi.status() == WL_CONNECTED){
    String dataTemp = "Temp=" + String(temp) + "&Dev=" + String(id_device) + "&Date=" + fecha + "&Time=" + hora;
    HTTPClient http;
    http.begin(urlCargar);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.addHeader("Cookie", headerCookie);
    int httpCode = http.POST(dataTemp);
    String mensaje = http.getString();
    estado = estado & mensaje=="OK";
    http.end();
    #ifdef DEBUG
    Serial.println(httpCode);
    Serial.println(mensaje);
    #endif
  }else{
    estado = false;
  }
  digitalWrite(LED, LOW);
  return estado;
}

boolean loadConfig(){
  digitalWrite(LED, HIGH);
  boolean estado = true;
  if(WiFi.status() == WL_CONNECTED){
    String dataConfig = "id=" + String(id_device);
    HTTPClient http;
    http.begin(urlConfig + "?" + dataConfig);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.addHeader("Cookie", headerCookie);
    int httpCode = http.GET();
    String mensaje = http.getString();
    estado = estado & (splitString(mensaje,0)=="OK");
    if(estado){
      gmtOffset = splitString(mensaje,1).toInt();
      delta_seg = splitString(mensaje,2).toInt();
      delta_temp = splitString(mensaje,3).toInt();
      loadDataState = splitString(mensaje,4)=="0"?false:true;
    }
    configTime(gmtOffset, 0, "pool.ntp.org");
    if(!getLocalTime(&timeinfo)){
      estado = false;
      timeState = false;
      #ifdef DEBUG
      Serial.println("Error de asignación de hora");
      #endif
    }else{
      timeState = true;
    }
    http.end();
    #ifdef DEBUG
    Serial.println(httpCode);
    Serial.println(mensaje);
    Serial.println(&timeinfo, "%A, %B %d %Y %H:%M:%S");
    #endif
  }else{
    estado = false;
  }
  digitalWrite(LED, LOW);
  return estado;
}

boolean startSession(){
  digitalWrite(LED, HIGH);
  boolean estado = true;
  if(WiFi.status() == WL_CONNECTED){
    const char * headerkeys[] = {"Set-Cookie"} ;
    size_t headerkeyssize = sizeof(headerkeys)/sizeof(char*);
    String dataSession = "usuarioLogin="+user+"&passLogin="+pass_user+"&btnLogin=Login&origen=sensor";
    
    HTTPClient http;
    
    http.begin(urlLogin);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.collectHeaders(headerkeys,headerkeyssize);
    int httpCode = http.POST(dataSession);
    String mensaje = http.getString();
    estado = estado & http.hasHeader("Set-Cookie");
    if(http.hasHeader("Set-Cookie")){
      headerCookie=http.header("Set-Cookie"); 
    }else{
      headerCookie="";
    }
    http.end();
    estado = estado & mensaje=="OK";
    #ifdef DEBUG
    Serial.println(dataSession);
    Serial.println(httpCode);
    Serial.println(mensaje);
    Serial.println(String(http.headers()));
    Serial.println(headerCookie);
    #endif
  }else{
    estado = false;
  }
  digitalWrite(LED, LOW);
  return estado;
}

boolean closeSession(){
  digitalWrite(LED, HIGH);
  boolean estado = true;
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    http.begin(urlLogin + "?cerrar=1&origen=sensor");
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.addHeader("Cookie", headerCookie);
    int httpCode = http.GET();
    String mensaje = http.getString();
    http.end();
    estado = estado & mensaje=="CLOSE";
    #ifdef DEBUG
    Serial.println(httpCode);
    Serial.println(mensaje);
    #endif
  }else{
    estado = false;
  }
  digitalWrite(LED, LOW);
  return estado;
}

void wifiConfig(){
  WiFi.begin(ssdi, pass_ssdi);
  while(WiFi.status() != WL_CONNECTED){
    #ifdef DEBUG
    Serial.println("Conectando...");
    #endif
    digitalWrite(LED, HIGH);
    delay(1000);
  }
  #ifdef DEBUG
  Serial.println(WiFi.localIP());
  Serial.println("Conexión exitosa");
  #endif
  digitalWrite(LED, LOW);
}

double leerTemperatura(){
  int RawValue = analogRead(ANALOG);
  double Voltage = (RawValue / 2048.0) * 3300;
  double tempC = Voltage * 0.1;
  return tempC;
}

int getButton(int s, int tps){
  int veces = 0;
  boolean estado = true;
  while(estado){
    delay((int)(1000/tps));
    estado = estado & digitalRead(BUTTON) & veces+1<(s*tps);
    veces = veces + digitalRead(BUTTON);
    #ifdef DEBUG
    Serial.print(String((int)(veces/tps)) + "-");
    #endif
  }
  return (int)(veces/tps);
}

void blinkblink(int t, int del){
  for(int i=0; i<t; i++){
    digitalWrite(LED, HIGH);
    delay(del);
    digitalWrite(LED, LOW);
    delay(del);
  }
}

String splitString(String cadena, int pos){
  int blankPos = 0;
  int lastblank = 0;
  for(int i=0; i<cadena.length(); i++){
    if((cadena[i] == ' ' || i==cadena.length()-1) && pos>=0){
      if(blankPos != lastblank){
        lastblank = blankPos;
      }
      blankPos = i;
      pos--;
    }
  }
  if(lastblank != 0) lastblank++;
  if(blankPos == cadena.length()-1) blankPos++;
  return cadena.substring(lastblank, blankPos);
}
