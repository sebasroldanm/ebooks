<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Repositories\BookRepository;
use App\Http\Controllers\AppBaseController;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Storage;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

class BookController extends AppBaseController
{
    /** @var  BookRepository */
    private $bookRepository;

    public function __construct(BookRepository $bookRepo)
    {
        $this->bookRepository = $bookRepo;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the Book.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->bookRepository->pushCriteria(new RequestCriteria($request));
        $books = $this->bookRepository->paginate(10);

        return view('books.index')
            ->with('books', $books);
    }

    /**
     * Show the form for creating a new Book.
     *
     * @return Response
     */
    public function create()
    {
        $authors = Author::pluck('name', 'id');
        return view('books.create', compact('authors'));
    }

    /**
     * Store a newly created Book in storage.
     *
     * @param CreateBookRequest $request
     *
     * @return Response
     */
    public function store(CreateBookRequest $request)
    {
        $input = $request->all();

        $book = $this->bookRepository->create($input);

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            // $nameFile = $file->getClientOriginalName();
            // $extension = $file->extension();
            $route = Storage::url($file->store('public/Img/Books'));
            $book->fill([
                'cover' => $route
            ])->save();
        }

        Flash::success('Book saved successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Display the specified Book.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $book = $this->bookRepository->findWithoutFail($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        return view('books.show')->with('book', $book);
    }

    /**
     * Show the form for editing the specified Book.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $book = $this->bookRepository->findWithoutFail($id);
        $authors = Author::pluck('name', 'id');

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        return view('books.edit', compact('book', 'authors'));
    }

    /**
     * Update the specified Book in storage.
     *
     * @param  int              $id
     * @param UpdateBookRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateBookRequest $request)
    {
        $book = $this->bookRepository->findWithoutFail($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        $book = $this->bookRepository->update($request->all(), $id);

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            // $nameFile = $file->getClientOriginalName();
            // $extension = $file->extension();
            $route = Storage::url($file->store('public/Img/Books'));
            $book->fill([
                'cover' => $route
            ])->save();
        }

        Flash::success('Book updated successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Remove the specified Book from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $book = $this->bookRepository->findWithoutFail($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        $this->bookRepository->delete($id);

        Flash::success('Book deleted successfully.');

        return redirect(route('books.index'));
    }
}
