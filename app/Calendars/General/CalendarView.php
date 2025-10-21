<?php
namespace App\Calendars\General;

use Carbon\Carbon;
use Auth;

class CalendarView{

  private $carbon;
  function __construct($date){
    $this->carbon = new Carbon($date);
  }

  public function getTitle(){
    return $this->carbon->format('Y年n月');
  }

  function render(){
    $html = [];
    $html[] = '<div class="calendar text-center">';
    $html[] = '<table class="table">';
    $html[] = '<thead>';
    $html[] = '<tr>';
    $html[] = '<th>月</th>';
    $html[] = '<th>火</th>';
    $html[] = '<th>水</th>';
    $html[] = '<th>木</th>';
    $html[] = '<th>金</th>';
    $html[] = '<th>土</th>';
    $html[] = '<th>日</th>';
    $html[] = '</tr>';
    $html[] = '</thead>';
    $html[] = '<tbody>';
    $weeks = $this->getWeeks();
    foreach($weeks as $week){
      $html[] = '<tr class="'.$week->getClassName().'">';

      $days = $week->getDays();
      foreach($days as $day){
        $startDay = $this->carbon->copy()->format("Y-m-01");
        $toDay = $this->carbon->copy()->format("Y-m-d");

        if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
          $html[] = '<td class="calendar-td bg-secondary">';
        }else{
          $html[] = '<td class="calendar-td '.$day->getClassName().'">';
        }
        $html[] = $day->render();

        $label = "受付終了";
        if(in_array($day->everyDay(), $day->authReserveDay())){
          $reservePart = $day->authReserveDate($day->everyDay())->first()->setting_part;
          $reserve_Id = $day->authReserveDate($day->everyDay())->first()->id;
          if($reservePart == 1){
            $reservePart = "リモ1部";
            $label = "1部参加";
          }else if($reservePart == 2){
            $reservePart = "リモ2部";
            $label = "2部参加";
          }else if($reservePart == 3){
            $reservePart = "リモ3部";
            $label = "3部参加";
          }
          if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
            $html[] = '<p class="m-auto p-0 w-75" style="font-size:12px">'.$label.'</p>';
            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
          }else{
            $reserve = $day->authReserveDate($day->everyDay())->first()->setting_reserve;
            $html[] = '<button type="button" id="delete-model-open-'.$reserve.'" reserve_id="'.$reserve_Id.'"  class="day-delete-modal-open btn btn-danger p-0 w-75" name="delete_date" style="font-size:12px" >'. $reservePart .'</button>';
            $html[] = '
            <div class="modal delete-js-modal">
              <div class="modal__bg js-modal-close"></div>
              <div class="modal__content">
                <form class="delete-form" method="post" action="/delete/calendar">
                '.csrf_field().'
                  <div class="w-100">
                    <div class="modal-inner-body text-left w-50 m-auto pt-3 pb-3">
                      <p>予約日：'.$reserve.'</p>
                      <p>時間：'.$reservePart.'</p>
                      <p>上記の予約をキャンセルしてよろしいですか？</p>
                    </div>
                    <div class="w-50 m-auto delete-modal-btn d-flex">
                      <a class="js-modal-close btn btn-primary d-inline-block" href="">閉じる</a>
                      <input type="hidden" class="delete-modal-hidden-reserveId" name="reserve_id" value="">
                      <input type="submit" class="btn btn-danger d-block" value="キャンセル">
                      <input type="hidden" name="getPart[]" value="" form="reserveParts">
                    </div>
                  </div>
                </form>
              </div>
            </div>
            ';
          }
        }else{
          if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
            $html[] = '<p class="m-auto p-0 w-75" style="font-size:12px">'.$label.'</p>';
            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
          }else{
            $html[] = $day->selectPart($day->everyDay());
          }
        }
        $html[] = $day->getDate();
        $html[] = '</td>';
      }
      $html[] = '</tr>';
    }
    $html[] = '</tbody>';
    $html[] = '</table>';
    $html[] = '</div>';
    $html[] = '<form action="/reserve/calendar" method="post" id="reserveParts">'.csrf_field().'</form>';
    $html[] = '<form action="/delete/calendar" method="post" id="deleteParts">'.csrf_field().'</form>';

    return implode('', $html);
  }

  protected function getWeeks(){
    $weeks = [];
    $firstDay = $this->carbon->copy()->firstOfMonth();
    $lastDay = $this->carbon->copy()->lastOfMonth();
    $week = new CalendarWeek($firstDay->copy());
    $weeks[] = $week;
    $tmpDay = $firstDay->copy()->addDay(7)->startOfWeek();
    while($tmpDay->lte($lastDay)){
      $week = new CalendarWeek($tmpDay, count($weeks));
      $weeks[] = $week;
      $tmpDay->addDay(7);
    }
    return $weeks;
  }
}
