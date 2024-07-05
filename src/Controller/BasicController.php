<?php

namespace App\Controller;

use App\Repository\TownRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasicController extends AbstractController
{
    /**
     * @Route("/basic/towns", name="app_basic_towns")
     */
    public function towns(): Response
    {
        return $this->render('basic/list.html.twig');
    }

    /**
     * @Route("/basic/datatable-list", name="app_basic_datatable_list", methods={"POST"})
     */
    public function datatableList(Request $request, TownRepository $repository): Response
    {
        $draw = $request->request->getInt('draw');
        $start = $request->request->getInt('start');
        $length = $request->request->getInt('length');
        $orders = $request->request->get('order');
        $search = $request->request->get('search');
        $columns = $request->request->get('columns');

        $result = $repository->getDatatableData($start, $length, $orders, $search, $columns);
        $records = $result['records'];
        $recordsFiltered = $result['totalFiltered'];

        $data = [];

        foreach ($records as $record) {
            $row = [];
            $row[] = $record->getName();
            $row[] = $record->getPostalCode();
            $row[] = $record->getDepartment()->getName();
            $row[] = $record->getDepartment()->getRegion()->getName();
            $data[] = $row;
        }

        $recordsTotal = $repository->count([]);

        $result = [
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];
        $json = json_encode($result);

        return new Response($json);
    }
    /*
    // ajax POST request from DataTables to /basic/datatable-list
    Array
    (
        [draw] => 1
        [columns] => Array
            (
                [0] => Array
                    (
                        [data] => 0
                        [name] => name
                        [searchable] => true
                        [orderable] => true
                        [search] => Array
                            (
                                [value] =>
                                [regex] => false
                            )
                    )

                [1] => Array
                    (
                        [data] => 1
                        [name] => postalCode
                        [searchable] => true
                        [orderable] => true
                        [search] => Array
                            (
                                [value] =>
                                [regex] => false
                            )
                    )

                [2] => Array
                    (
                        [data] => 2
                        [name] => department
                        [searchable] => true
                        [orderable] => true
                        [search] => Array
                            (
                                [value] =>
                                [regex] => false
                            )
                    )

                [3] => Array
                    (
                        [data] => 3
                        [name] => region
                        [searchable] => true
                        [orderable] => true
                        [search] => Array
                            (
                                [value] =>
                                [regex] => false
                            )

                    )

            )

        [order] => Array
            (
                [0] => Array
                    (
                        [column] => 2
                        [dir] => asc
                    )

            )

        [start] => 0
        [length] => 10
        [search] => Array
            (
                [value] =>
                [regex] => false
            )

    )

     */
}
