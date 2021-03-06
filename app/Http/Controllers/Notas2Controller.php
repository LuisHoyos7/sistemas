<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNotas2Request;
use App\Http\Requests\UpdateNotas2Request;
use App\Repositories\Notas2Repository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Notas2;
use Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

class Notas2Controller extends AppBaseController
{
    /** @var  Notas2Repository */
    private $notas2Repository;

    public function __construct(Notas2Repository $notas2Repo)
    {
        $this->notas2Repository = $notas2Repo;
    }

    /**
     * Display a listing of the Notas2.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
         
        session()->put('id_asignatura',$request->get('id_asignatura'));
        session()->put('grupo',$request->get('grupo'));
        session()->put('parametro1',$request->get('parametro1'));
        session()->put('parametro2',$request->get('parametro2'));
         $id_asignatura = session()->get('id_asignatura');
         $grupo = session()->get('grupo');
         $parametro1 = session()->get('parametro1');
         $parametro2 = session()->get('parametro2');

         $notas = Notas2::orderBy('id','DESC')
         ->id($id_asignatura)
         ->grupo($grupo)
         ->corte2($parametro1,$parametro2)
         ->paginate(10);

         $pdfs = Notas2::orderBy('id','DESC')
         ->id($id_asignatura)
         ->grupo($grupo)
         ->corte2($parametro1,$parametro2)
         ->paginate(6000);

         
         $excel = Notas2::orderBy('asignatura','ASC')
         ->id($id_asignatura)
         ->grupo($grupo)
         ->corte2($parametro1,$parametro2)
         ->paginate(6000);
          
         session()->put('excel',$excel);

          $asignaturas = Notas2::orderBy('asignatura')->pluck('asignatura', 'id_asignatura');
          $grupos = Notas2::orderBy('grupo')->pluck('grupo', 'grupo');
         

         session()->put('search',compact('pdfs','asignaturas','grupos'));

         return view('notas2s.index',compact('notas','asignaturas','grupos'));
    }

    /**
     * Show the form for creating a new Notas2.
     *
     * @return Response
     */
    public function create()
    {
        return view('notas2s.create');
    }

    /**
     * Store a newly created Notas2 in storage.
     *
     * @param CreateNotas2Request $request
     *
     * @return Response
     */
    public function store(CreateNotas2Request $request)
    {
        $input = $request->all();

        $notas2 = $this->notas2Repository->create($input);

        Flash::success('Notas2 saved successfully.');

        return redirect(route('notas2s.index'));
    }

    /**
     * Display the specified Notas2.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $notas2 = $this->notas2Repository->findWithoutFail($id);

        if (empty($notas2)) {
            Flash::error('Notas2 not found');

            return redirect(route('notas2s.index'));
        }

        return view('notas2s.show')->with('notas2', $notas2);
    }

    /**
     * Show the form for editing the specified Notas2.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $notas2 = $this->notas2Repository->findWithoutFail($id);

        if (empty($notas2)) {
            Flash::error('Notas2 not found');

            return redirect(route('notas2s.index'));
        }

        return view('notas2s.edit')->with('notas2', $notas2);
    }

    /**
     * Update the specified Notas2 in storage.
     *
     * @param  int              $id
     * @param UpdateNotas2Request $request
     *
     * @return Response
     */
    public function update($id, UpdateNotas2Request $request)
    {
        $notas2 = $this->notas2Repository->findWithoutFail($id);

        if (empty($notas2)) {
            Flash::error('Notas2 not found');

            return redirect(route('notas2s.index'));
        }

        $notas2 = $this->notas2Repository->update($request->all(), $id);

        Flash::success('Notas2 updated successfully.');

        return redirect(route('notas2s.index'));
    }

    /**
     * Remove the specified Notas2 from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $notas2 = $this->notas2Repository->findWithoutFail($id);

        if (empty($notas2)) {
            Flash::error('Notas2 not found');

            return redirect(route('notas2s.index'));
        }

        $this->notas2Repository->delete($id);

        Flash::success('Notas2 deleted successfully.');

        return redirect(route('notas2s.index'));
    }

    public function pdf(Request $request)
    {        
        $pdf = PDF::loadView('reportes.notas2', session('search'));
        return $pdf->stream();
        //return $pdf->download('listado.pdf');
    }
    public function excel(){
         // estas dos lineas permiten descargar y abrir el archivo excel sin errores en el formato y la extension de este //

            ob_end_clean (); 
            ob_start (); 
            
           return \Excel::create('LISTADO DE ESTUDIANTES CON NOTAS ENTRE '.session()->get('parametro1').' Y '.session()->get('parametro2'), function($excel)   {
                
            $excel->sheet('REPORTE_ESTUDIANTES', function ($sheet) {
            
            $sheet->mergeCells('A1:D1');
            $sheet->row(1,['LISTADO DE ESTUDIANTES']);
            $sheet->ROW(2,['ID_ASIGNATURA','ASIGNATURA','GRUPO',
                           'DOCENTE','ID_ESTUDIANTE','ESTUDIANTE','COHORTE 1','COHORTE 2','COHORTE 3']);
            
            $consulta = session()->get('excel');
            

          
            foreach($consulta as $cons){
                    $row = [];

                    $row[0] = $cons->id_asignatura;
                    $row[1] = $cons->asignatura;
                    $row[2] = $cons->grupo;
                    $row[3] = $cons->docente;
                    $row[4] = $cons->id_estudiante;
                    $row[5] = $cons->estudiante;
                    $row[6] = $cons->corte1;
                    $row[7] = $cons->corte2;
                    $row[8] = $cons->corte3;
                        
                    $sheet->appendRow($row);

            }

            
        });
                
        })->export('XLSX');

    }
}


