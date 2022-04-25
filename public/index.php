<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Working with local dates</title>
    <!-- AXIOS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.24.0/axios.min.js" integrity="sha512-u9akINsQsAkG9xjc1cnGF4zw5TFDwkxuc9vUp5dltDWYCSmyd0meygbvgXrlc/z7/o4a19Fb5V0OUE58J7dcyw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- DAYJS -->
    <script src="https://unpkg.com/dayjs@1.10.7/dayjs.min.js"></script>
    <script src="https://unpkg.com/dayjs@1.10.7/plugin/utc.js"></script>
    <script src="https://unpkg.com/dayjs@1.10.7/plugin/timezone.js"></script>

    <style>
        body {
            padding: 100px;
            padding-top: 60px;
        }

        #title {
            text-align: center;
        }

        #timeZoneNote {
            border: 1px solid black;
            width: 50%;
            margin: 0px auto;
            margin-top: 30px;
            padding: 10px;
        }

        table {
            margin: 30px auto 0px;
            width: 80%;
            text-align: center;
        }

        #twoWaysTable th {
            width: 50%;
            text-transform: uppercase;
            padding: 10px;
        }

        #twoWaysTable td {
            text-align: left;
            padding: 15px;
        }

        td {
            padding: 5px;
            vertical-align: top;
        }

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        #message {
            color: red;
        }

        #divFrom,
        #divTo {
            display: flex;
            width: 400px;
        }

        .flex-one {
            flex: 1;
        }

        .flex-two {
            flex: 2;
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <h1 id="title">WORKING WITH LOCAL DATES (PHP, POSTGRESQL AND JAVASCRIPT/Dayjs)</h1>
    <h3 style="text-decoration: underline">THEORY</h3>
    <p>In the following table I will describe two possible ways of dealing with local dates:</p>
    <table id="twoWaysTable">
        <tr>
            <th>Normalizing dates to UTC with aid of the JS datetime library 'Dayjs'</th>
            <th>Without use of any external JS datetime library</th>
        </tr>
        <tr>
            <td colspan="2">
                <strong style="text-decoration: underline">Inserting a new date</strong><br>
                To make things easy, we will always save our dates in a column of type 'timestamptz' (= UTC) in our PostgreSQL table, no matter which one of the two approaches we choose.<br>
                Therefore, to insert a new date we can build the UTC date string with PHP like this:
                <pre>
                    $serverDate = date('Y-m-d H:i:s');              // e.g. "2021-12-30 04:12:18"
                    $date = new DateTime($serverDate);              // DateTime object with server date
                    $date->setTimezone(new DateTimeZone('UTC'));    // DateTime object with UTC date
                    $utcDate = $date->format('c');                  // e.g. "2021-12-30T12:12:18+00:00"
                </pre>
                And then save it with a prepared statement:
                <pre>
                    $sql = "INSERT INTO dates (date) VALUES (:date)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':date' => "2021-12-30T12:12:18+00:00"]);
                </pre>
                Or we can simply call the PostgreSQL function NOW(), which uses a string of that moment that is time zone aware:
                <pre>
                    $sql = "INSERT INTO dates (date) VALUES (NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([]);
                </pre>

            </td>
        </tr>
        <tr>
            <td>
                <strong style="text-decoration: underline">Retrieval of date</strong><br>
                1. In PostgreSQL we format the date as we need it in Dayjs to create a Dayjs object. In this example, I have used the <span><a href='https://day.js.org/docs/en/parse/string' target="_blank">following possibility</a></span>, so this is the format in PostgreSQL:<br>
                <pre>
                    to_char(date, 'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"') as "dayjsutc"
                </pre>
                2. Then we create the Dayjs object and format it as we want it:
                <pre>
                    let dayjsObject = dayjs(dayjsutc);
                    let localDate = dayjsObject.format();
                </pre>

            </td>
            <td>
                <strong style="text-decoration: underline">Retrieval of date</strong><br>
                1. In PostgreSQL we format the date as we need it for <a href='https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse#date_time_string_format' target="_blank">Date.parse to consider it as UTC</a>:
                <pre>
                    to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MSOF:"00"\') as "formatted_utc"
                </pre>
                2. Then we create a Javascript Date object and format it as we want it:
                <pre>
                    let formatted_utc = element.formatted_utc;
                    let dateObject = new Date(Date.parse(formatted_utc));
                    let dateStringOptions = {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                        timeZone: 'Europe/Madrid'
                    };
                    let datetimeFormatObject = new Intl.DateTimeFormat('es-ES', dateStringOptions);

                    window.dateObjects.push(dateObject);
                    let localDate = datetimeFormatObject.format(dateObject); // es un string
                </pre>
            </td>
        </tr>
        <tr>
            <td>
                <strong style="text-decoration: underline">Filtering dates</strong><br>
                1. First, you create two DayJS objects with the starting and ending dates:<br>
                <pre>
                    let localObjectStartDate = dayjs(fullDateFrom, 'YYYY-MM-DD HH:mm');
                    let localObjectEndDate = dayjs(fullDateUntil, 'YYYY-MM-DD HH:mm');
                </pre>
                2. Then we create ISO strings from the Dayjs objects and call the endpoint that filters the dates passing the ISO strings as parameters:
                <pre>
                    let localObjectStartDateString = localObjectStartDate.toISOString();
                    let localObjectEndDateString = localObjectEndDate.toISOString();
                    axios.get('/filter-dates-by-utc.php', {
                            params: { // date from and to must be in UTC
                                dateFrom: localObjectStartDateString,
                                dateUntil: localObjectEndDateString
                            }
                        })
                        .then(function(response) {
                        })
                        .catch(function(error) {
                        });
                </pre>

            </td>
            <td>
                <strong style="text-decoration: underline">Filtering dates</strong><br>
                1. First, you get the values of the date and time inputs and form a date string (without time zone):<br>
                <pre>
                    let dateFrom = document.querySelector('#dateFrom').value;
                    let dateUntil = document.querySelector('#dateUntil').value;
                    let timeFrom = document.querySelector('#timeFrom').value;
                    let timeUntil = document.querySelector('#timeUntil').value;
                    let fullDateFrom = dateFrom + ' ' + timeFrom;
                    let fullDateUntil = dateUntil + ' ' + timeUntil;
                </pre>
                2. Then we call the endpoint that filters the dates passing the date strings with the timezone as parameters:
                <pre>
                    axios.get('/filter-dates-by-localtime.php', {
                        params: { // since date from and to are not in UTC, then time zone needs to be passed as well
                            dateFrom: fullDateFrom,
                            dateUntil: fullDateUntil,
                            timezone: 'Europe/Madrid'
                        }
                    })
                    .then(function(response) {
                    })
                    .catch(function(error) {
                    });
                </pre>

            </td>
        </tr>
    </table>
    <p id="timeZoneNote">
        <em>NOTE: For educational purposes, I use the browser time zone as the desired initial time zone in the following practical case. <br><br>
            In apps where there is a time zone setting under the user configuration page - in other words, where the time zone choice may be different than the browser time zone - you could retrieve a User entity containing the user time zone choice in the PHP controller and send it as a JSON to the frontend to take the desired time zone value from there.</em>
    </p>
    <h3 style="text-decoration: underline">PRACTICAL CASE</h3>
    <p>- Choose an approach:</p>
    <input type="radio" id="dayjsradio" value="dayjs" name="chosenApproach" checked="checked">
    <label for="dayjsradio">I'm a cool type, give me normalizing with UTC and Dayjs power...</label><br>
    <input type="radio" id="nodayjsradio" value="nodayjs" name="chosenApproach">
    <label for="nodayjsradio">I'm tough, no external JS libraries for me!</label>
    <br><br>
    <p>- Insert new dates (works the same for both approaches):</p>
    <button id='insertDateBtn'>Insert date on DB</button>
    <br><br>
    <p>- Filter dates by browser timezone (same result but done differently depending on approach chosen):</p>
    <div id="divFrom">
        <label for="from" class="flex-one">From: </label><input type="date" id="dateFrom" class="flex-two"><input type="time" id="timeFrom" class="flex-one" value="00:00">
    </div>
    <br>
    <div id="divTo">
        <label for="to" class="flex-one">To: </label><input type="date" id="dateTo" class="flex-two"><input type="time" id="timeTo" class="flex-one" value="23:59">
    </div>
    <br><br>
    <button id='filterDatesBtn'>Filter dates</button>
    <br><br>
    <p id="message"></p>
    <table id='datesTable'></table>

</body>

<script>
    // Implementing needed Dayjs plugins: https://stackoverflow.com/a/64319608
    const utc = window.dayjs_plugin_utc;
    const timezone = window.dayjs_plugin_timezone;
    dayjs.extend(utc);
    dayjs.extend(timezone);

    // ELEMENTS
    const insertDateBtn = document.querySelector('#insertDateBtn');
    const message = document.querySelector('#message');
    const datesTable = document.querySelector('#datesTable');
    const filterDatesBtn = document.querySelector('#filterDatesBtn');
    const inputDateFrom = document.querySelector('#dateFrom');
    const inputDateUntil = document.querySelector('#dateTo');
    const inputTimeFrom = document.querySelector('#timeFrom');
    const inputTimeUntil = document.querySelector('#timeTo');

    // GLOBAL VARS
    window.dayjsObjects = [];
    window.dateObjects = [];
    window.browserTimezone = dayjs.tz.guess() // E.g.: 'Pacific/Kiritimati' --> other apps could take timezone from somewhere different than the browser timezone
    console.log('Browser timezone: ' + window.browserTimezone);
    window.availableTimezones = [];
    window.dateStringOptions = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        timeZone: window.browserTimezone
    };
    window.chosenApproach = 'dayjs';

    // WHEN REFRESHING THE PAGE, ALL DATES FROM DB ARE SHOWN. IT WILL FOLLOW THE dayjs APPROACH.
    axios.get('/index-data.php')
        .then(function(response) {
            //console.log(response);
            window.availableTimezones = response.data.availableTimezones;
            renderDatesOnTable(response.data.dates, window.availableTimezones);
            setTimeZoneSelectListener();
            setApproachListener();
        })
        .catch(function(error) {
            console.log(error);
        });

    // INSERTION WORKS THE SAME WAY IN BOTH APPROACHES. THE RENDERING AFTER INSERTION FOLLOWS THE DAYJS APPROACH ONLY
    insertDateBtn.onclick = () => {

        axios.get('/insert-date.php')
            .then(function(response) {
                //console.log(response);
                renderDatesOnTable(response.data.dates, window.availableTimezones);
                setTimeZoneSelectListener();
            })
            .catch(function(error) {
                console.log(error);
            });
    };

    filterDatesBtn.onclick = () => {

        /*
        PHP equivalent to what's done in Dayjs:
        $dia = date("Y-m-d");
        $initialDate = new DateTime($dia. '00:00:00', new DateTimeZone('Pacific/Nauru'));
        $finalDate = new DateTime($dia. '23:59:59', new DateTimeZone('Pacific/Nauru'));
        var_dump($initialDate);
        */

        // Making sure dates are valid...
        let dateFrom = inputDateFrom.value;
        let dateUntil = inputDateUntil.value;
        //console.log('dateFrom ' + dateFrom)
        //console.log('dateUntil ' + dateUntil)
        if (dateFrom === '' || dateUntil === '') {
            message.innerHTML = 'Make sure you choose two dates (from and to) before clicking \'Filter dates\'.';
            return;
        }

        console.log('browserTimezone: ' + window.browserTimezone);

        let timeFrom = inputTimeFrom.value;
        let timeUntil = inputTimeUntil.value;
        let fullDateFrom = dateFrom + ' ' + timeFrom;
        let fullDateUntil = dateUntil + ' ' + timeUntil;
        //console.log('fullDateFrom ' + fullDateFrom)
        //console.log('fullDateUntil ' + fullDateUntil)

        let localObjectStartDate = dayjs(fullDateFrom, 'YYYY-MM-DD HH:mm'); // https://day.js.org/docs/en/parse/string-format
        let localObjectEndDate = dayjs(fullDateUntil, 'YYYY-MM-DD HH:mm'); // https://day.js.org/docs/en/timezone/converting-to-zone

        if (localObjectEndDate.isBefore(localObjectStartDate)) {
            message.innerHTML = 'Make sure the first date you choose is an earlier date than the second one.';
            return;
        }

        // Dates are valid, so let's get dates filtered from db...
        message.innerHTML = '';

        if (window.chosenApproach === 'dayjs') {
            let localObjectStartDateString = localObjectStartDate.toISOString();
            console.log('localObjectStartDateString ' + localObjectStartDateString);
            let localObjectEndDateString = localObjectEndDate.toISOString();
            console.log('localObjectEndDateString ' + localObjectEndDateString);

            axios.get('/filter-dates-by-utc.php', {
                    params: { // date from and to must be in UTC
                        dateFrom: localObjectStartDateString,
                        dateUntil: localObjectEndDateString
                    }
                })
                .then(function(response) {
                    renderDatesOnTable(response.data.dates, window.availableTimezones);
                    setTimeZoneSelectListener();
                })
                .catch(function(error) {
                    //console.log(error);
                });
        } else if (window.chosenApproach === 'nodayjs') {
            axios.get('/filter-dates-by-localtime.php', {
                    params: { // if date from and to are not in UTC, then time zone needs to be passed as well
                        dateFrom: fullDateFrom,
                        dateUntil: fullDateUntil,
                        timezone: window.browserTimezone
                    }
                })
                .then(function(response) {
                    renderDatesOnTable(response.data.dates, window.availableTimezones);
                    setTimeZoneSelectListener();
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    };

    /* Listener to call method that shows dates for the time zone selected when timezone select changes its value */
    function setTimeZoneSelectListener() {
        const timezonesSelect = document.querySelector('#timezonesSelect');
        timezonesSelect.onchange = () => {
            renderDatesForChosenTimezone();
        }
    }

    /* Changes the global variable chosenApproach when one input radio of the chosenApproach name is selected */
    function setApproachListener() {
        let inputsChosenApproach = document.querySelectorAll('input[type=radio][name=chosenApproach]');
        inputsChosenApproach.forEach(input => {
            input.onclick = (event) => {
                window.chosenApproach = event.target.defaultValue;
            }
        });
    }

    /* It renders the dates on UTC and local/browser formats */
    function renderDatesOnTable(dates, availableTimezones) {

        if (dates.length === 0) {
            message.innerHTML = 'No results shown. Two possible reasons: no date has yet been registered or there are no results for the filtered dates.';
        } else {
            message.innerHTML = '';
        }

        let zoneSelect = document.createElement('select');
        zoneSelect.id = 'timezonesSelect';
        availableTimezones.forEach(zone => {
            let option = document.createElement('option');
            option.value = zone;
            option.innerHTML = zone;
            if (zone === window.browserTimezone) {
                option.defaultSelected = true;
            }
            zoneSelect.appendChild(option);
        });

        datesTable.innerHTML = '';
        datesTable.insertAdjacentHTML('beforeend', `<tr><th>UTC</th><th>${browserTimezone} <br>(Browser time zone)</th><th>` + zoneSelect.outerHTML + '</th></tr>');


        window.dayjsObjects = []; // Clear previous Dayjs objects, if there are any
        window.dateObjects = [];

        if (window.chosenApproach === 'dayjs') {

            dates.forEach(element => {
                let UTCdate = element.date; // UTC string as it comes from timezonetz column (without any TO_CHAR)
                //console.log(UTCdate)
                let dayjsutc = element.dayjsutc; // UTC string previously formatted in PostgreSQL with TO_CHAR so that it comes with format needed to create a Dayjs object
                //console.log(dayjsutc)

                let dayjsObject = dayjs(dayjsutc);
                window.dayjsObjects.push(dayjsObject);
                //console.log(dayjsObject)
                let localDate = dayjsObject.format();

                datesTable.insertAdjacentHTML('beforeend', `<tr><td>${UTCdate}</td><td>${localDate}</td><td>${localDate}</td></tr>`);
            });

        } else if (window.chosenApproach === 'nodayjs') {

            dates.forEach(element => {

                let UTCdate = element.date; // UTC string as it comes from timezonetz column (without any TO_CHAR)
                let formatted_utc = element.formatted_utc; // UTC string previously formatted in PostgreSQL with TO_CHAR so that it comes with format needed for Date.parse to consider it as UTC: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse#date_time_string_format

                let dateObject = new Date(Date.parse(formatted_utc));
                let datetimeFormatObject = new Intl.DateTimeFormat('es-ES', dateStringOptions);

                window.dateObjects.push(dateObject);
                let localDate = datetimeFormatObject.format(dateObject); // es un string

                datesTable.insertAdjacentHTML('beforeend', `<tr><td>${UTCdate}</td><td>${localDate}</td><td>${localDate}</td></tr>`);
            });
        }
    }

    /* Renders the third column with the dates formatted according to the timezone selected from the select element */
    function renderDatesForChosenTimezone() {

        let chosenTimezone = document.querySelector('#timezonesSelect').value;
        let datesTable = document.querySelector('#datesTable');

        /* Since the dates could have been saved as dayjs objects or as pure js objects, we check both cases */
        if (window.dayjsObjects.length !== 0) {
            window.dayjsObjects.forEach((dayjsObject, index) => {
                let trIndex = index + 1;
                let cell = datesTable.querySelectorAll('tr')[trIndex].querySelectorAll('td')[2];

                let timezoneObject = dayjsObject.tz(chosenTimezone); // https://day.js.org/docs/en/timezone/converting-to-zone
                cell.innerHTML = timezoneObject.format();
            });
        } else if (window.dateObjects.length !== 0) {

            window.dateStringOptions.timeZone = chosenTimezone;

            window.dateObjects.forEach((dateObject, index) => {
                let trIndex = index + 1;
                let cell = datesTable.querySelectorAll('tr')[trIndex].querySelectorAll('td')[2];

                let datetimeFormatObject = new Intl.DateTimeFormat('es-ES', dateStringOptions);
                cell.innerHTML = datetimeFormatObject.format(dateObject);
            });
        }
    }
</script>

</html>