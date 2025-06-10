import mysql.connector
from datetime import datetime,timedelta, time

conn = mysql.connector.connect(
    host="localhost",
    database="zeitmanagement",
    user="admin",
    password="Projektmanagement"
)

cursor = conn.cursor(dictionary=True)

def fix_no_gehen(work_day, arbeits_zeit):
    print(f"no \"Gehen\" on {work_day[-1][1]}")
    print("fixing")


    soll_arbeits_zeit = timedelta(hours=int(user["sollArbeitszeit"])/5)
    diff = soll_arbeits_zeit - arbeits_zeit
    gehen_time = work_day[-1][1]+diff;

    
    print(f"arbeits zeit: {arbeits_zeit} soll arbeits zeit: {soll_arbeits_zeit} diff: {diff}")
    print(f"inserting at {work_day[-1][1]+diff}")
    if (gehen_time.date() != work_day[-1][1].date()) :
        gehen_time = datetime.combine(work_day[-1][1].date(), time(23,59))
    

    cursor.execute("INSERT INTO zeiterfassung (benutzer_id,aktion,zeitpunkt) VALUES (%s,%s,%s)",(user["id"],"Gehen",work_day[-1][1]+diff))
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



def evaluate_current_day(work_day, user,arbeits_zeit): 
    delta = datetime.today() - work_day[-1][1]
    arbeits_zeit += delta
    print("--- current day ---")

    if arbeits_zeit.seconds > 32400:
        print("you need to take another break");
    elif arbeits_zeit.seconds > 32000:
        print("you need to take another break soon")
    elif arbeits_zeit.seconds > 21600:
        print("you need to take a break")
    elif arbeits_zeit.seconds > 21000:
        print("you need to take a break soon")
    return arbeits_zeit


def handle_user(user:dict) :

    #return a dict key = date values = [(aktion,time)]
    worked_days = get_worked_days(user["id"])
    
    # iterate over every day to calculate arbeits_zeit and to fix possible issues
    for work_day in worked_days.values():

        arbeits_zeit,deltas = calculate_arbeits_zeit_pairs(work_day)


        # if the day we are working on right now is not the current day
        # you can alter the table to fix some inconsisntencies
        if work_day[-1][1].date() != datetime.today().date():

            # if the last entry in the day is a kommen- the user didn't log out propberly 
            if work_day[-1][0] == "Kommen":
                fix_no_gehen(work_day,arbeits_zeit)

            # if the time between a kommen and a gehen is too big
            # we need to insert a break in between
            if deltas != [] and max(deltas)[0].seconds > 21600:
                fix_large_delta(work_day[0][1].date(),max(deltas))

        elif work_day[-1][0] == "Kommen":
            arbeits_zeit = evaluate_current_day(work_day,user,arbeits_zeit)

        print(f"{work_day[0][1].date()}:{arbeits_zeit}")




cursor.execute("SELECT * FROM user ORDER BY id")
for user in cursor.fetchall():
    handle_user(user)





cursor.close()
conn.close()