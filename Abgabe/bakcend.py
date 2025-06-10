import mysql.connector
from datetime import datetime,timedelta, time

conn = mysql.connector.connect(
    host="localhost",
    database="zeitmanagement",
    user="admin",
    password="Projektmanagement"
)

cursor = conn.cursor(dictionary=True)

def fix_no_gehen(work_day, arbeits_zeit, alter):
    print(f"no \"Gehen\" on {work_day[-1][1]}")
    print("fixing")

    if alter >= 13 and alter <= 15:
        print("young user max 2h")
        gehen_time = work_day[-1][1]+timedelta(hours=2)
    else:

        soll_arbeits_zeit = timedelta(hours=int(user["sollArbeitszeit"])/5)
        diff = soll_arbeits_zeit - arbeits_zeit
        gehen_time = work_day[-1][1]+diff;

    
    if (gehen_time.date() != work_day[-1][1].date()) :
        gehen_time = datetime.combine(work_day[-1][1].date(), time(23,59))
    
    print(f"arbeits zeit: {arbeits_zeit} soll arbeits zeit: {soll_arbeits_zeit} diff: {diff}")
    print(f"inserting at {gehen_time}")

    cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Gehen",gehen_time))
    conn.commit()

def fix_large_delta(date,max_delta):
    print(f"on day {date} there was a delta that was too big delta: {max_delta[1]}")
    print("fixing")
    middle = max_delta[1] + (max_delta[2] - max_delta[1])/2
    print(f"middle {middle}")
    print("inserting \"Gehen\"")
    cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Gehen",middle))
    conn.commit()
    middle += timedelta(seconds=1800)
    print(f"middle {middle}")
    print("inserting a \"Kommen\"")
    cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Kommen",middle))
    conn.commit()

def fix_large_delta_young(date,max_delta,age):
    diff = max_delta[2]-max_delta[1]
    # the time the user worked for less than 6h
    if diff.seconds < 21600:
        fix_large_delta(date,max_delta)
    else:
        print(f"user is too young max_delta is {max_delta[0]}")
        middle = max_delta[1] + (max_delta[2] - max_delta[1])/2
        print(f"beginn is {max_delta[1]} end is {max_delta[2]} middle is {middle}")
        print(f"inserting gehen at {middle}")
        cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Gehen",middle))
        conn.commit()
        middle += timedelta(seconds=3600)

        print("user needs to take 60min break")
        print(f"insertig kommen at {middle}")

        cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Kommen",middle))
        conn.commit()


def get_worked_days(user_id) :
    cursor.execute("SELECT * FROM zeiterfassung WHERE benutzer_id = %s ORDER BY zeitpunkt",(user_id,))
    worked_days = {}
    for row in cursor.fetchall():
        day = row["zeitpunkt"]
        if not day.date() in worked_days:
            worked_days[day.date()] = []
        worked_days[day.date()].append((row["aktion"],day))
    
    return worked_days

def calculate_arbeits_zeit_pairs(work_day):
    arbeits_zeit = timedelta(0)
    deltas = []

    previous = work_day[0]
    for current in work_day:
        if previous[0] == "Kommen" and current[0] == "Gehen":
            delta = current[1] - previous[1]
            deltas.append((delta,previous[1],current[1]))
            arbeits_zeit += delta
        previous = current
    return (arbeits_zeit,deltas)

def berechne_alter(user_dict):
    geburtstag = user_dict.get("geburtstag")
    
    heute = datetime.today().date()
    
    alter = heute.year - geburtstag.year
    
    # Prüfen, ob der Geburtstag dieses Jahr schon war
    if (heute.month, heute.day) < (geburtstag.month, geburtstag.day):
        alter -= 1
    
    return alter

def send_notification(text,user:dict):
    cursor.execute("select erzeugt_am from notification where benutzer_id = %s and text = %s order by erzeugt_am desc limit 1;",(user["id"],text))
    last_notification_time = cursor.fetchone()
    if last_notification_time != None:
        last_notification_time = last_notification_time["erzeugt_am"]
        if (datetime.today() - last_notification_time).seconds < 10800:
            print(f"too soon for {text}")
            return

    cursor.execute("INSERT INTO notification (benutzer_id,text,gelesen,erzeugt_am) VALUES (%s,%s,%s,%s)",(user["id"],text,0,datetime.today()))

def user_abmelden(user):
    print("insert gehen")
    cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUE (%s,%s,%s)",(user["id"],"Gehen",datetime.today()))
    cursor.execute("UPDATE user SET status = 0 WHERE id = %s",(user["id"],))
    conn.commit()

    send_notification("002",user)

def evaluate_current_day(work_day, user:dict,arbeits_zeit): 
    delta = datetime.today() - work_day[-1][1]
    arbeits_zeit += delta
    print("--- current day ---")
    alter = berechne_alter(user)


    if (alter >= 13 and alter <= 15):
        if arbeits_zeit.seconds >= 7200:
            print("13-15 user worked too much")
            user_abmelden(user)
        elif arbeits_zeit.seconds >= 6900:
            print("you need to stop working soon")
            send_notification("001",user)
        return
    elif (alter >= 15 and alter <= 18):
        if (arbeits_zeit.seconds >= 28800):
            print("15-18 user worked too much")
            user_abmelden(user)
        elif (arbeits_zeit.seconds >= 28200):
            print("you need to stop working soon")
            send_notification("001",user)
        elif (arbeits_zeit.seconds >= 21600):
            print("you need to take a break")
            send_notification("000",user)
        elif (arbeits_zeit.seconds >= 21000):
            print("you need to take another break soon")
            send_notification("003",user)
        elif (arbeits_zeit.seconds >= 16200):
            print("you need to take a break now")
            send_notification("000",user)
        elif (arbeits_zeit.seconds >= 15600):
            print("you need to take a break soon")
            send_notification("001",user)

    if arbeits_zeit.seconds > 32400:
        print("you need to take another break");
        send_notification("003",user)
    elif arbeits_zeit.seconds > 32000:
        print("you need to take another break soon")
        send_notification("001",user)
    elif arbeits_zeit.seconds > 21600:
        print("you need to take a break")
        send_notification("000",user)
    elif arbeits_zeit.seconds > 21000:
        print("you need to take a break soon")
        send_notification("001",user)
    return arbeits_zeit


def handle_user(user:dict) :

    if datetime.today().time().hour == 0 and datetime.today().time().minute <= 5:
        current_konto = user["konto"]
        current_konto -= float(user["sollArbeitszeit"]/5)
        cursor.execute("UPDATE user SET konto = %s WHERE id = %s",(current_konto,user["id"]))

    #return a dict key = date values = [(aktion,time)]
    worked_days = get_worked_days(user["id"])
    
    # iterate over every day to calculate arbeits_zeit and to fix possible issues
    for work_day in worked_days.values():

        arbeits_zeit,deltas = calculate_arbeits_zeit_pairs(work_day)


        # if the day we are working on right now is not the current day
        # you can alter the table to fix some inconsisntencies
        if work_day[-1][1].date() != datetime.today().date():
            alter = berechne_alter(user)

            # if the last entry in the day is a kommen- the user didn't log out propberly 
            if work_day[-1][0] == "Kommen":
                fix_no_gehen(work_day,arbeits_zeit,alter)

            # if the time between a kommen and a gehen is too big
            # we need to insert a break in between
            if alter >= 15 and alter < 18:
                if deltas != [] and max(deltas)[0].seconds > 16200:
                    fix_large_delta_young(work_day[0][1].date(),max(deltas),alter)
            elif deltas != [] and max(deltas)[0].seconds > 21600:
                fix_large_delta(work_day[0][1].date(),max(deltas))

        elif work_day[-1][0] == "Kommen":
            arbeits_zeit = evaluate_current_day(work_day,user,arbeits_zeit)
            konto_time = float(user["konto"])
            konto_time += 1/60
            print(f"konto time {konto_time}")
            cursor.execute("UPDATE user SET konto = %s where id = %s",(konto_time,user["id"]))
            conn.commit()


        print(f"{work_day[0][1].date()}:{arbeits_zeit}")




cursor.execute("SELECT * FROM user ORDER BY id")
for user in cursor.fetchall():
    handle_user(user)





cursor.close()
conn.close()