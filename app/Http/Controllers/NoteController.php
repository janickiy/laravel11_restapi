<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notes\StoreRequest;
use App\Http\Requests\Notes\UpdateRequest;
use App\Models\Notes;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/notes",
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
     *     path="/api/notes/{id}",
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
        $note = Notes::where('id', $id)->wheer('user_id', $user_id)->first();

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }

    /**
     * @OA\Post(
     *     path="/api/notes",
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

        // Очистка кэша после добавления новой заметки
        Cache::forget("notes.user.{$user_id}");

        // Логирование события создания заметки
        Log::info('Note created', ['user_id' => $user_id, 'note_id' => $note->id]);

        return response()->json($note, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/notes/{id}",
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
    public function update(UpdateRequest $request): JsonResponse
    {
        $user_id = Auth::user()->id;

        $note = Notes::find($request->id);

        if (!$note) {
            return response()->json(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        $note->title = $request->input('title');
        $note->description = $request->input('description');
        $note->save();

        // Очистка кэша после обновления заметки
        Cache::forget("notes.user.{$user_id}");

        // Логирование события обновления заметки
        Log::info('Note updated', ['user_id' => $user_id, 'note_id' => $note->id]);

        return response()->json($note);
    }

    /**
     * @OA\Delete(
     *     path="/api/notes/{id}",
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
        $user_id = Auth::user()->id;

        Notes::destroy($id);

        // Очистка кэша после удаления заметки
        Cache::forget("notes.user.{ $user_id}");

        // Логирование события удаления заметки
        Log::info('Note deleted', ['user_id' => $user_id, 'note_id' => $id]);

        return response()->json(['message' => 'Note deleted']);
    }
}
