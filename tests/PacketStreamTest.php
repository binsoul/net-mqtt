<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PacketStreamTest extends TestCase
{
    private const string STRING_UNICODE = 'Hello 世界';

    private const string DATA_BINARY = "\x00\x01\x02\xFF";

    private const string DATA_HELLO = 'Hello';

    private const string DATA_WORLD = 'World';

    public function test_constructs_empty_stream(): void
    {
        $stream = new PacketStream('');

        $this->assertSame(0, $stream->length());
        $this->assertSame('', $stream->getData());
        $this->assertSame(0, $stream->getPosition());
    }

    public function test_constructs_with_initial_data(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $this->assertSame(strlen(self::DATA_HELLO), $stream->length());
        $this->assertSame(self::DATA_HELLO, $stream->getData());
        $this->assertSame(self::DATA_HELLO, (string) $stream);
    }

    public function test_reads_exact_number_of_bytes(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $result = $stream->read(strlen(self::DATA_HELLO));

        $this->assertSame(self::DATA_HELLO, $result);
        $this->assertSame(strlen(self::DATA_HELLO), $stream->getPosition());
    }

    public function test_reads_partial_data(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $result = $stream->read(2);

        $this->assertSame('He', $result);
        $this->assertSame(2, $stream->getPosition());
    }

    public function test_reads_sequentially(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $first = $stream->read(2);
        $second = $stream->read(3);

        $this->assertSame('He', $first);
        $this->assertSame('llo', $second);
        $this->assertSame(5, $stream->getPosition());
    }

    public function test_read_throws_exception_at_end_of_stream(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(strlen(self::DATA_HELLO));

        $this->expectException(EndOfStreamException::class);

        $stream->read(1);
    }

    public function test_read_throws_exception_when_not_enough_data(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $this->expectException(EndOfStreamException::class);

        $stream->read(strlen(self::DATA_HELLO) * 2);
    }

    public function test_read_throws_exception_when_position_exceeds_length(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->setPosition(10);

        $this->expectException(EndOfStreamException::class);

        $stream->read(10);
    }

    public function test_reads_byte_as_integer(): void
    {
        $stream = new PacketStream("\x00\xFF\x80");

        $this->assertSame(0, $stream->readByte());
        $this->assertSame(255, $stream->readByte());
        $this->assertSame(128, $stream->readByte());
    }

    public function test_reads_word_as_integer(): void
    {
        $stream = new PacketStream("\x00\x00\xFF\xFF\x80\x00");

        $this->assertSame(0, $stream->readWord());
        $this->assertSame(65535, $stream->readWord());
        $this->assertSame(32768, $stream->readWord());
    }

    public function test_read_word_throws_exception_at_end_of_stream(): void
    {
        $stream = new PacketStream("\x00");

        $this->expectException(EndOfStreamException::class);

        $stream->readWord();
    }

    public function test_reads_length_prefixed_string(): void
    {
        $stream = new PacketStream("\x00\x05Hello");

        $result = $stream->readString();

        $this->assertSame(self::DATA_HELLO, $result);
        $this->assertSame(7, $stream->getPosition());
    }

    public function test_reads_empty_string(): void
    {
        $stream = new PacketStream("\x00\x00");

        $result = $stream->readString();

        $this->assertSame('', $result);
        $this->assertSame(2, $stream->getPosition());
    }

    public function test_read_string_throws_exception_when_length_prefix_missing(): void
    {
        $stream = new PacketStream("\x00");

        $this->expectException(EndOfStreamException::class);

        $stream->readString();
    }

    public function test_read_string_throws_exception_when_data_incomplete(): void
    {
        $stream = new PacketStream("\x00\x05Hel");

        $this->expectException(EndOfStreamException::class);

        $stream->readString();
    }

    public function test_writes_string_data(): void
    {
        $stream = new PacketStream('');

        $stream->write(self::DATA_HELLO);

        $this->assertSame(self::DATA_HELLO, $stream->getData());
        $this->assertSame(strlen(self::DATA_HELLO), $stream->length());
    }

    public function test_writes_appends_data(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->write(self::DATA_WORLD);

        $this->assertSame(self::DATA_HELLO . self::DATA_WORLD, $stream->getData());
        $this->assertSame(strlen(self::DATA_HELLO . self::DATA_WORLD), $stream->length());
    }

    public function test_writes_binary_data(): void
    {
        $stream = new PacketStream('');

        $stream->write(self::DATA_BINARY);

        $this->assertSame(self::DATA_BINARY, $stream->getData());
    }

    public function test_writes_byte_as_character(): void
    {
        $stream = new PacketStream('');

        $stream->writeByte(0);
        $stream->writeByte(255);
        $stream->writeByte(128);

        $this->assertSame("\x00\xFF\x80", $stream->getData());
    }

    public function test_writes_word_as_two_bytes(): void
    {
        $stream = new PacketStream('');

        $stream->writeWord(0);
        $stream->writeWord(65535);
        $stream->writeWord(32768);

        $this->assertSame("\x00\x00\xFF\xFF\x80\x00", $stream->getData());
    }

    public function test_writes_word_with_high_byte_first(): void
    {
        $stream = new PacketStream('');

        $stream->writeWord(258);

        $this->assertSame("\x01\x02", $stream->getData());
    }

    public function test_writes_length_prefixed_string(): void
    {
        $stream = new PacketStream('');

        $stream->writeString(self::DATA_HELLO);

        $this->assertSame("\x00\x05Hello", $stream->getData());
    }

    public function test_writes_empty_string_with_zero_length(): void
    {
        $stream = new PacketStream('');

        $stream->writeString('');

        $this->assertSame("\x00\x00", $stream->getData());
    }

    public function test_writes_unicode_string(): void
    {
        $stream = new PacketStream('');

        $stream->writeString(self::STRING_UNICODE);

        $expectedLength = strlen(self::STRING_UNICODE);
        $this->assertSame("\x00" . chr($expectedLength) . self::STRING_UNICODE, $stream->getData());
    }

    public function test_write_string_throws_exception_when_too_long(): void
    {
        $stream = new PacketStream('');
        $longString = str_repeat('a', 65535 + 1);

        $this->expectException(InvalidArgumentException::class);

        $stream->writeString($longString);
    }

    public function test_returns_stream_length(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $this->assertSame(strlen(self::DATA_HELLO), $stream->length());
    }

    public function test_returns_remaining_bytes(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(2);

        $this->assertSame(3, $stream->getRemainingBytes());
    }

    public function test_returns_zero_remaining_bytes_at_end(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(strlen(self::DATA_HELLO));

        $this->assertSame(0, $stream->getRemainingBytes());
    }

    public function test_returns_zero_remaining_bytes_beyond_end(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->setPosition(strlen(self::DATA_HELLO) * 2);

        $this->assertSame(0, $stream->getRemainingBytes());
    }

    public function test_returns_whole_data(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(2);

        $this->assertSame(self::DATA_HELLO, $stream->getData());
    }

    public function test_seeks_forward(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->seek(2);

        $this->assertSame(2, $stream->getPosition());
    }

    public function test_seeks_backward(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(strlen(self::DATA_HELLO));

        $stream->seek(-3);

        $this->assertSame(2, $stream->getPosition());
    }

    public function test_seek_prevents_negative_position(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->seek(-10);

        $this->assertSame(0, $stream->getPosition());
    }

    public function test_seek_allows_position_beyond_length(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->seek(10);

        $this->assertSame(10, $stream->getPosition());
    }

    public function test_gets_current_position(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(3);

        $this->assertSame(3, $stream->getPosition());
    }

    public function test_sets_position_directly(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->setPosition(3);

        $this->assertSame(3, $stream->getPosition());
    }

    public function test_set_position_prevents_negative_value(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->setPosition(-5);

        $this->assertSame(0, $stream->getPosition());
    }

    public function test_cuts_data_from_beginning_to_position(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(2);

        $stream->cut();

        $this->assertSame('llo', $stream->getData());
        $this->assertSame(0, $stream->getPosition());
    }

    public function test_cut_removes_all_data_when_position_at_end(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(strlen(self::DATA_HELLO));

        $stream->cut();

        $this->assertSame('', $stream->getData());
        $this->assertSame(0, $stream->getPosition());
    }

    public function test_cut_does_nothing_when_position_at_beginning(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);

        $stream->cut();

        $this->assertSame(self::DATA_HELLO, $stream->getData());
        $this->assertSame(0, $stream->getPosition());
    }

    public function test_cut_handles_position_beyond_length(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->setPosition(10);

        $stream->cut();

        $this->assertSame('', $stream->getData());
        $this->assertSame(0, $stream->getPosition());
    }

    public function test_handles_mixed_read_write_operations(): void
    {
        $stream = new PacketStream(self::DATA_HELLO);
        $stream->read(2);
        $stream->write(self::DATA_WORLD);

        $this->assertSame(self::DATA_HELLO . self::DATA_WORLD, $stream->getData());
        $this->assertSame(2, $stream->getPosition());
        $this->assertSame(8, $stream->getRemainingBytes());
    }

    public function test_handles_complex_workflow(): void
    {
        $stream = new PacketStream('');
        $stream->writeByte(255);
        $stream->writeWord(32768);
        $stream->writeString(self::DATA_HELLO);

        $stream->setPosition(0);

        $this->assertSame(255, $stream->readByte());
        $this->assertSame(32768, $stream->readWord());
        $this->assertSame(self::DATA_HELLO, $stream->readString());
    }
}
