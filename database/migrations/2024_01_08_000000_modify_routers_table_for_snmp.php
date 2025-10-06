<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // Remove RouterOS API fields
            $table->dropColumn(['username', 'password', 'port']);
            
            // Add SNMP fields
            $table->string('snmp_community')->default('public');
            $table->enum('snmp_version', ['1', '2c', '3'])->default('2c');
            $table->integer('snmp_port')->default(161);
            
            // SNMP v3 specific fields (optional)
            $table->string('snmp_v3_username')->nullable();
            $table->string('snmp_v3_auth_protocol')->nullable(); // MD5, SHA
            $table->string('snmp_v3_auth_password')->nullable();
            $table->string('snmp_v3_priv_protocol')->nullable(); // DES, AES
            $table->string('snmp_v3_priv_password')->nullable();
            $table->enum('snmp_v3_security_level', ['noAuthNoPriv', 'authNoPriv', 'authPriv'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // Restore RouterOS API fields
            $table->string('username');
            $table->string('password');
            $table->integer('port')->default(8728);
            
            // Remove SNMP fields
            $table->dropColumn([
                'snmp_community',
                'snmp_version',
                'snmp_port',
                'snmp_v3_username',
                'snmp_v3_auth_protocol',
                'snmp_v3_auth_password',
                'snmp_v3_priv_protocol',
                'snmp_v3_priv_password',
                'snmp_v3_security_level'
            ]);
        });
    }
};
