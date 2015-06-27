/* 
 * John Harold Belalcazar Lozano
 * Shared js functionality
 */
$(document).ready(function(){
   
    //$('.result').DataTable();
           
    $("#txtsrch").focus();
    $("#btnSearch").click(function(){
        if ($.trim($("#srch-term").val())==""){
            alert("Please write an actor's name to start a search");
            return false;
        }
    });
    
    $('.fancybox-media').fancybox({
        openEffect  : 'elastic',
        closeEffect : 'elastic',
        helpers : {
         media : {}
        }
    });
    
    asignSearch();

});

function asignSearch(){
    if ($('#selectsearch').val()=="1")
    {
        Cookies.set('selsearch', '1', { expires: 1 });
        $('#srch-term').typeahead({
          displayKey: 'value',
          header: '<b>Actor suggestions...</b>',
          limit: 15,
          minLength: 3,
          remote: {
              url : 'http://api.themoviedb.org/3/search/person?query=%QUERY&api_key=22e8595de8c06be02009d7efb076399e',
              filter: function (parsedResponse) {               
                  retval = [];
                  debugger;
                  for (var i = 0;  i < parsedResponse.results.length;  i++) {
                      retval.push({
                          value: parsedResponse.results[i].name,
                          tokens: [parsedResponse.results[i].name]
                      });
                  }
                  return retval;
              },
              dataType: 'jsonp'
          }
        });    
    } else{
        Cookies.set('selsearch', '2', { expires: 1 });
        $('#srch-term').typeahead({
          displayKey: 'value',
          header: '<b>Movie suggestions...</b>',
          limit: 15,
          minLength: 3,
          remote: {
              url : 'http://api.themoviedb.org/3/search/movie?query=%QUERY&api_key=22e8595de8c06be02009d7efb076399e',
              filter: function (parsedResponse) {               
                  retval = [];
                  debugger;
                  for (var i = 0;  i < parsedResponse.results.length;  i++) {
                      retval.push({
                          value: parsedResponse.results[i].original_title,
                          tokens: [parsedResponse.results[i].original_title]
                      });
                  }
                  return retval;
              },
              dataType: 'jsonp'
          }
        });    
        
    }    
}