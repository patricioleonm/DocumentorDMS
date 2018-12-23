  $(document).ready(() => {
    //add dashlet buttons
    var template_button = document.getElementById("dashlets-button");
    var content = template_button.content.cloneNode(true);
    var col = document.getElementById("dashlets-col");
    col.appendChild(content);

    //sortable
    $("#dashboard-columns").sortable({
      beforeStop: (event, ui) => {
      }
    });

    //close buttons
    $(".close").on("click", (e) => {
      var card = e.currentTarget.parentNode.parentNode;      
      hideDashlet(card.id);
    });

    $(".dropdown-item").on("click", (e) => {
      showDashlet(e.currentTarget.attributes["data-id"].value);
    });

    window.addEventListener("beforeunload", function (event) {
      $.ajax({
        url : window.location.pathname,
        type: "POST",
        data: {"action" :"json", "json_action":"saveDashboardState", "state" : JSON.stringify(getDashletsArray())}        
      });

      (event || window.event).returnValue = null;
      return null;
    });

    function showDashlet(dashlet){
      document.getElementById(dashlet).classList.remove("d-none");
      document.getElementById(dashlet).classList.add("d-inline-block");
      document.querySelector("a[data-id='"+dashlet+"']").classList.remove("d-block");    
      document.querySelector("a[data-id='"+dashlet+"']").classList.add("d-none");
      dashboard_state[dashlet] = true;
    }

    function hideDashlet(dashlet){
      document.getElementById(dashlet).classList.remove("d-inline-block");
      document.getElementById(dashlet).classList.add("d-none");
      document.querySelector("a[data-id='"+dashlet+"']").classList.remove("d-none");    
      document.querySelector("a[data-id='"+dashlet+"']").classList.add("d-block");
      dashboard_state[dashlet] = false;
    }

    function getDashletsArray(){
      var cards = document.querySelectorAll(".card");
      var sortedDashlets = {};
      Object.values(cards)
        .filter((element) => element.id != "")
        .forEach((card) => {
          sortedDashlets[card.id] = dashboard_state[card.id];
        });
      return sortedDashlets;
    }

    function doUpdates(){
      Object.entries(dashboard_state).forEach(element => {
        if(element[1]){
          showDashlet(element[0]);
        }else{
          hideDashlet(element[0]);
        }
      });
    }

    doUpdates();
    
  });