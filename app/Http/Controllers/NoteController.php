<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notes\StoreRequest;
use App\Http\Requests\Notes\UpdateRequest;
use App\Models\Notes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */


    /**
     * @OA\Get(
     *     path="/api/v1/notes",
     *     summary="Get all notes",
     *     @OA\Response(
     *         response=200,
     *         description="A list of notes",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Test Note"),
     *             @OA\Property(property="content", type="string", example="This is a test note."),
     *             @OA\Property(property="created_at", type="string", example="2024-05-20T14:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2024-05-20T14:00:00.000000Z")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $notes = Notes::all();

        return response()->json($notes);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notes/{id}",
     *     summary="Get a specific note",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A note",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Test Note"),
     *             @OA\Property(property="content", type="string", example="This is a test note."),
     *             @OA\Property(property="created_at", type="string", example="2024-05-20T14:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2024-05-20T14:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user_id = Auth::user()->id;
        $note = Notes::where('id', $id)->where('user_id', $user_id)->first();

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notes/store",
     *     summary="Create a new note",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string", example="Test Note"),
     *             @OA\Property(property="content", type="string", example="This is a test note.")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Note created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Test Note"),
     *             @OA\Property(property="content", type="string", example="This is a test note."),
     *             @OA\Property(property="created_at", type="string", example="2024-05-20T14:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2024-05-20T14:00:00.000000Z")
     *         )
     *     )
     * )
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user_id = Auth::user()->id;
        $note = Notes::create(array_merge($request->all(), ['user_id' => $user_id]));

        return response()->json($note, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notes/update/{id}",
     *     summary="Update an existing note",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Test Note"),
     *             @OA\Property(property="content", type="string", example="This is an updated test note.")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Updated Test Note"),
     *             @OA\Property(property="content", type="string", example="This is an updated test note."),
     *             @OA\Property(property="created_at", type="string", example="2024-05-20T14:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", example="2024-05-20T14:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     )
     * )
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $note = Notes::find($id);

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        $note->title = $request->input('title');
        $note->content = $request->input('content');
        $note->save();

        return response()->json($note);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notes/delete/{id}",
     *     summary="Delete a note",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        Notes::destroy($id);

        return response()->json(['message' => 'Note deleted']);
    }
}
