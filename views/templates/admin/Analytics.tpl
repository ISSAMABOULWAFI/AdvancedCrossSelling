


<div class="constainer">
    <div class="panel">
            <section id="auth-button"></section>
            <br/>
            <section id="view-selector"></section>

        <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for names.." title="Type in a name">
            <div style="height: 500px;overflow: scroll">
                <a id="timeline"></a>
            </div>

    </div>
</div>
<!-- Step 2: Load the library. -->


<!-- -->
<script>
    {literal}
    $( function() {
    $( "#datepicker" ).datepicker({ dateFormat: 'yyyy-mm-dd'});
    } )
    {/literal}
</script>

<script>
    {literal}
    (function(w,d,s,g,js,fjs){
        g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(cb){this.q.push(cb)}};
        js=d.createElement(s);fjs=d.getElementsByTagName(s)[0];
        js.src='https://apis.google.com/js/platform.js';
        fjs.parentNode.insertBefore(js,fjs);js.onload=function(){g.load('analytics')};
    }(window,document,'script'));
    {/literal}
</script>

<script>
    {literal}
    gapi.analytics.ready(function() {

        // Step 3: Authorize the user.

        var CLIENT_ID = '629732304947-aqajru45ubr4fja33dsnm1lst2qdjbe4.apps.googleusercontent.com';

        gapi.analytics.auth.authorize({
            container: 'auth-button',
            clientid: CLIENT_ID,
        });

        // Step 4: Create the view selector.

        var viewSelector = new gapi.analytics.ViewSelector({
            container: 'view-selector'
        });

        // Step 5: Create the timeline chart.

        var timeline = new gapi.analytics.googleCharts.DataChart({
            reportType: 'ga',
            query: {
                dimensions: 'ga:campaign',
                metrics: 'ga:sessions,ga:transactions,ga:transactionRevenue,ga:bounceRate',
                'start-date': '30daysAgo',
                'end-date': 'yesterday',
            },
            chart: {
                type: 'TABLE',
                container: 'timeline',
                options: {
                    fontSize: 12,
                    width: '100%'
                }

            }
        });

        // Step 6: Hook up the components to work together.

        gapi.analytics.auth.on('success', function(response) {
            viewSelector.execute();
        });

        viewSelector.on('change', function(ids) {
            var newIds = {
                query: {
                    ids: ids
                }
            }
            timeline.set(newIds).execute();
        });
    });

    function myFunction() {
        var input, filter, table, tr, td, i;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        table = document.getElementsByClassName("google-visualization-table-table");
        tr = document.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    {/literal}
</script>
