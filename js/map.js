am4core.useTheme(am4themes_animated);

var index;
var oldIndex = 0;
var sliderAnimation;
var currCountry = "World";
var years;
var active;
var currTable;
var coulmnButtons;
var buttonsAndChartContainer;
var countryName;

// Create a container
var container = am4core.create("chartdiv", am4core.Container);
container.width = am4core.percent(100);
container.height = am4core.percent(100);
container.logo.height = -15;

//container.layout = "vertical";
container.tooltip = new am4core.Tooltip();
container.tooltip.background.fill = am4core.color("#000000");
container.tooltip.fontSize = "0.9em";
container.tooltip.getFillFromObject = false;
container.tooltip.getStrokeFromObject = false;

// Create map
var map = container.createChild(am4maps.MapChart);
map.geodata = am4geodata_worldLow;
map.projection = new am4maps.projections.Miller();

// Create map polygon series
var polygonSeries = map.series.push(new am4maps.MapPolygonSeries());

// Make map load polygon (like country names) data from GeoJSON
polygonSeries.useGeodata = true;

// Configure series
var polygonTemplate = polygonSeries.mapPolygons.template;
polygonTemplate.tooltipText = "{name}\n" + active + "{value}";
polygonTemplate.fill = am4core.color("#444");
polygonTemplate.events.on("hit", function (ev) {
    let name = ev.target.dataItem.dataContext.name;
    if (name == countryName.text)
        updateCountryName("World");
    else {
        updateCountryName(name);
        currCountry = name;
    }
});

// Create hover state and set alternative fill color
var hs = polygonTemplate.states.create("hover");
hs.properties.fill = am4core.color("#aaaaaa");
polygonSeries.exclude = ["AQ"];
polygonSeries.heatRules.push({
    "property": "fill",
    "target": polygonSeries.mapPolygons.template,
    "min": am4core.color("#444"),
    "max": am4core.color("#FBB117"),
});
map.background.fill = am4core.color("#333");
map.background.fillOpacity = 1;

// table selection container
var tableSelection = container.createChild(am4core.Container);
tableSelection.layout = "vertical";
tableSelection.height = am4core.percent(20);
tableSelection.width = am4core.percent(100);
tableSelection.valign = "top";

// create buttons
var tableButtons = {};
var settings = {
    "url": "https://lucabancale.netsons.org/api/product/schema.php",
    "method": "POST",
    "timeout": 0,
    "headers": {
        "Content-Type": "text/plain"
    }
};
jQuery.ajax(settings).done(function (response) {
    response.forEach(element => {
        if (element != "countries") {
            tableButtons[element] = addButton(element, tableSelection, "changeTable");
        }
    });
    currTable = response[0];
    years = getYears();

    goOn();
}).fail(function (response) {
    polygonSeries.data = {};
});
// i have to wait for years to be defined
function goOn() {
    // buttons & chart container
    buttonsAndChartContainer = container.createChild(am4core.Container);
    buttonsAndChartContainer.layout = "vertical";
    buttonsAndChartContainer.height = am4core.percent(20);
    buttonsAndChartContainer.width = am4core.percent(100);
    buttonsAndChartContainer.valign = "bottom";

    // country name and buttons container
    var nameAndButtonsContainer = buttonsAndChartContainer.createChild(am4core.Container)
    nameAndButtonsContainer.layout = "vertical";
    nameAndButtonsContainer.width = am4core.percent(100);
    nameAndButtonsContainer.padding(0, 10, 5, 20);
    nameAndButtonsContainer.layout = "horizontal";

    // name of a country and date label
    countryName = nameAndButtonsContainer.createChild(am4core.Label);
    countryName.fontSize = "2em";
    countryName.fill = am4core.color("#ffffff");
    countryName.valign = "middle";
    updateCountryName(currCountry, active)

    // buttons container 
    var buttonsContainer = nameAndButtonsContainer.createChild(am4core.Container);
    buttonsContainer.layout = "grid";
    buttonsContainer.width = am4core.percent(100);
    buttonsContainer.x = 10;
    buttonsContainer.contentAlign = "right";

    var chartAndSliderContainer = buttonsAndChartContainer.createChild(am4core.Container);
    chartAndSliderContainer.layout = "vertical";
    chartAndSliderContainer.height = am4core.percent(100);
    chartAndSliderContainer.width = am4core.percent(100);
    chartAndSliderContainer.background = new am4core.RoundedRectangle();
    chartAndSliderContainer.background.fill = am4core.color("#000000");
    chartAndSliderContainer.background.cornerRadius(30, 30, 0, 0)
    chartAndSliderContainer.background.fillOpacity = 0.25;
    chartAndSliderContainer.paddingTop = 12;
    chartAndSliderContainer.paddingBottom = 0;

    // Slider container
    var sliderContainer = chartAndSliderContainer.createChild(am4core.Container);
    sliderContainer.width = am4core.percent(100);
    sliderContainer.padding(0, 15, 15, 10);
    sliderContainer.layout = "horizontal";

    var slider = sliderContainer.createChild(am4core.Slider);
    slider.width = am4core.percent(100);
    slider.valign = "middle";
    slider.background.opacity = 0.4;
    slider.opacity = 0.7;
    slider.background.fill = am4core.color("#ffffff");
    slider.marginLeft = 20;
    slider.marginRight = 35;
    slider.height = 15;
    slider.start = 0;

    // what to do when slider is stopped
    slider.events.on("rangechanged", function (event) {
        index = Math.round((years.length - 1) * slider.start);
        if (index != oldIndex) {
            updateMapData(years[index]);
            oldIndex = index;
            updateCountryName(currCountry, active)
        }
    })

    // stop animation if dragged
    slider.startGrip.events.on("drag", () => {
        stop();
        if (sliderAnimation) {
            sliderAnimation.setProgress(slider.start);
        }
    });
    // play button
    var playButton = sliderContainer.createChild(am4core.PlayButton);
    playButton.valign = "middle";
    // play button behavior
    playButton.events.on("toggled", function (event) {
        if (event.target.isActive) {
            play();
        } else {
            stop();
        }
    })
    // make slider grip look like play button
    slider.startGrip.background.fill = playButton.background.fill;
    slider.startGrip.background.strokeOpacity = 0;
    slider.startGrip.icon.stroke = am4core.color("#ffffff");
    slider.startGrip.background.states.copyFrom(playButton.background.states)

    // Add expectancy data
    /*polygonSeries.data = */
    polygonTemplate.propertyFields.fill = "fill";


    // BUTTONS
    // create buttons
    coulmnButtons = {};
    var settings = {
        "url": "https://lucabancale.netsons.org/api/product/table_schema.php?table=" + currTable,
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Content-Type": "text/plain"
        }
    };
    jQuery.ajax(settings).done(function (response) {
        response.forEach(element => {
            if (element != "ID" && element != "Country" && element != "Year") {
                coulmnButtons[element] = addButton(element, buttonsContainer, "changeColumn");
            }
        });
        active = response[3];
    }).fail(function (response) {
        polygonSeries.data = {};
    });

    // play behavior
    function play() {
        if (!sliderAnimation) {
            sliderAnimation = slider.animate({
                property: "start",
                to: 1,
                from: 0
            }, 50000, am4core.ease.linear).pause();
            sliderAnimation.events.on("animationended", () => {
                playButton.isActive = false;
            })
        }
        if (slider.start >= 1) {
            slider.start = 0;
            sliderAnimation.start();
        }
        sliderAnimation.resume();
        playButton.isActive = true;
    }
    // stop behavior
    function stop() {
        if (sliderAnimation) {
            sliderAnimation.pause();
        }
        playButton.isActive = false;
    }
}
// add button
function addButton(name, cont, func) {
    var button = cont.createChild(am4core.Button)
    button.label.text = name
    button.label.valign = "middle"
    button.label.fill = am4core.color("#ffffff");
    button.label.fontSize = "11px";
    button.background.cornerRadius(30, 30, 30, 30);
    button.background.strokeOpacity = 0.3
    button.background.fillOpacity = 0;
    button.background.padding(2, 3, 2, 3);
    button.states.create("active");
    button.setStateOnChildren = true;

    var activeHoverState = button.background.states.create("hoverActive");
    activeHoverState.properties.fillOpacity = 0;

    var circle = new am4core.Circle();
    circle.radius = 8;
    circle.fillOpacity = 0.3;
    circle.strokeOpacity = 0;
    circle.valign = "middle";
    circle.marginRight = 5;
    button.icon = circle;

    // save name to dummy data for later use
    button.dummyData = name;

    var circleActiveState = circle.states.create("active");
    //circleActiveState.properties.fill = #FBB117;
    circleActiveState.properties.fillOpacity = 0.5;
    if (func == "changeColumn") {
        button.events.on("hit", handleButtonClickColumn);
    } else if (func == "changeTable") {
        button.events.on("hit", handleButtonClickTable);
    }

    return button;
}


// handle button click
function handleButtonClickColumn(event) {
    // we saved name to dummy data
    changeDataType(event.target.dummyData);
}

// handle button click
function handleButtonClickTable(event) {
    // we saved name to dummy data
    changeData(event.target.dummyData);
}

// change data type
function changeDataType(name) {
    // make button active
    active = name;
    var activeButton = coulmnButtons[name];
    activeButton.isActive = true;
    // make other buttons inactive
    for (var key in coulmnButtons) {
        if (coulmnButtons[key] != activeButton) {
            coulmnButtons[key].isActive = false;
        }
    }
    updateMapData(years[oldIndex]);
}

// change data type
function changeData(name) {
    // make button active
    var activeButton = tableButtons[name];
    activeButton.isActive = true;
    // make other buttons inactive
    for (var key in coulmnButtons) {
        if (coulmnButtons[key] != activeButton) {
            coulmnButtons[key].isActive = false;
        }
    }
    currTable = name;
    buttonsAndChartContainer.dispose();
    goOn();
    updateMapData(years[oldIndex]);
}

function updateMapData(year) {
    var settings = {
        "url": "https://lucabancale.netsons.org/api/product/read.php?table=" + currTable + "&year=" + year,
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Content-Type": "text/plain"
        }
    };
    jQuery.ajax(settings).done(function (response) {
        var arr = []
        response.forEach(element => {
            arr.push({
                "id": element['ISO_char'],
                "value": +element[active]
            });
        });
        polygonSeries.data = arr;
        polygonTemplate.tooltipText = "{name}\n" + active + ": {value}";

        map.validateData();
    }).fail(function (response) {
        polygonSeries.data = {};
    });
}

function updateCountryName(country) {
    if (years == null) {
        countryName.text = country;
    } else {
        countryName.text = country + ", " + years[oldIndex];
    }
}

function getYears() {
    var settings = {
        "url": "https://lucabancale.netsons.org/api/product/years.php?table=" + currTable,
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Content-Type": "text/plain"
        }
    };
    jQuery.ajax(settings).done(function (response) {
        years = [];
        var first = parseInt(response[0]['Year'])
        var last = parseInt(response[response.length - 1]['Year'])
        for (var year = first; year <= last; year++) {
            years.push(year);
        }
        updateCountryName(currCountry);
        updateMapData(years[oldIndex])
    }).fail(function (response) {});
}