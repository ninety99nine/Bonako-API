<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\MediaFileRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\MediaFile\ShowMediaFilesRequest;
use App\Http\Requests\Models\MediaFile\UpdateMediaFileRequest;
use App\Http\Requests\Models\MediaFile\DeleteMediaFilesRequest;

class MediaFileController extends BaseController
{
    /**
     *  @var MediaFileRepository
     */
    protected $repository;

    /**
     * MediaFileController constructor.
     *
     * @param MediaFileRepository $repository
     */
    public function __construct(MediaFileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show media files.
     *
     * @param ShowMediaFileRequest $request
     * @return JsonResponse
     */
    public function showMediaFiles(ShowMediaFilesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showMediaFiles($request->all()));
    }

    /**
     * Delete media files.
     *
     * @param DeleteMediaFilesRequest $request
     * @return JsonResponse
     */
    public function deleteMediaFiles(DeleteMediaFilesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteMediaFiles($request->input('media_file_ids')));
    }

    /**
     * Show media file.
     *
     * @param string $mediaFileId
     * @return JsonResponse
     */
    public function showMediaFile(string $mediaFileId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showMediaFile($mediaFileId));
    }

    /**
     * Update media file.
     *
     * @param UpdateMediaFileRequest $request
     * @param string $mediaFileId
     * @return JsonResponse
     */
    public function updateMediaFile(UpdateMediaFileRequest $request, string $mediaFileId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateMediaFile($mediaFileId));
    }

    /**
     * Delete media file.
     *
     * @param string $mediaFileId
     * @return JsonResponse
     */
    public function deleteMediaFile(string $mediaFileId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteMediaFile($mediaFileId));
    }
}
